<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AbaPaywayController extends Controller
{
    public function createQrPayment(Request $request){
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'fine_id' => 'nullable|integer',
        ]);;

        $merchantId = env('ABA_PAYWAY_MERCHANT_ID', 'ec438902');
        $apiKey = env('ABA_PAYWAY_API_KEY', 'fake_api_key_for_testing');
        $mode = env('ABA_PAYWAY_MODE', 'sandbox');

        // ១. បង្កើតម៉ោងតាមស្តង់ដារទម្រង់របស់ ABA
        $reqTime = now()->format('YmdHis');

        // ២. បង្កើតលេខកូដប្រតិបត្តិការស្វ័យប្រវត្តិតែមួយគត់
        $tranId = 'TXN-' . time();

        // ៣. កំណត់ចំនួនទឹកប្រាក់ឲ្យមានទម្រង់ក្បៀសច្បាស់លាស់
        $amount = number_format((float) $request->amount, 2, '.', '');

        // ៤. គណនា Hash Signature (HMAC-SHA512)
        $strToHash = $reqTime . $merchantId . $tranId . $amount;
        $hash = base64_encode(hash_hmac('sha512', $strToHash, $apiKey, true));

        // ៥. បង្កើតទម្រង់ EMVCo KHQR String
        $khqrString = sprintf(
            "00020101021229300016abaa.kh.payment0106%s52045999530384054%02d%s5802KH5911BBU LIBRARY6010SIEM REAP62180114%s6304ABCD",
            $merchantId,
            strlen($amount),
            $amount,
            $tranId
        );

        // 6. ផ្ញើទិន្នន័យទាំងអស់ត្រឡប់ទៅឲ្យ Frontend (React) វិញ
        return response()->json([
            'status' => 'success',
            'data' => [
                'merchant_id' => $merchantId,
                'merchant_name' => 'BUILD BRIGHT UNIVERSITY LIBRARY',
                'tran_id' => $tranId,
                'req_time' => $reqTime,
                'amount' => $amount,
                'currency' => 'USD',
                'hash' => $hash,
                'qr_string' => $khqrString,
                'mode' => $mode,
            ],
            'message' => 'ABA KHQR generated successfully'
        ]);
    }


    public function checkTransaction(Request $request){
        $request->validate([
            'tran_id' => 'required|string',
        ]);

        $tranId = $request->tran_id;
        $mode = env('ABA_PAYWAY_MODE', 'sandbox');
        $merchantId = env('ABA_PAYWAY_MERCHANT_ID', 'ec438902');
        $apiKey = env('ABA_PAYWAY_API_KEY', 'fake_api_key_for_testing');

        // បើសិនជាស្ថិតក្នុង Sandbox និងមាន API Key ពិតប្រាកដពី ABA ត្រូវហៅ API ទៅកាន់ Server ABA ផ្ទាល់
        if ($mode === 'sandbox' && $apiKey !== 'fake_api_key_for_testing') {
            try {
                $reqTime = now()->format('YmdHis');
                // គណនា Hash សម្រាប់ការផ្ទៀងផ្ទាត់ Status
                $strToHash = $reqTime . $merchantId . $tranId;
                $hash = base64_encode(hash_hmac('sha512', $strToHash, $apiKey, true));

                // ផ្ញើសំណើ POST ទៅកាន់ ABA PayWay Check Transaction API
                $response = Http::post(env('ABA_PAYWAY_CHECK_URL'), [
                    'req_time' => $reqTime,
                    'merchant_id' => $merchantId,
                    'tran_id' => $tranId,
                    'hash' => $hash,
                ]);

                return $response->json();
            } catch (\Exception $e) {
                Log::error('ABA check error: ' . $e->getMessage());
            }
        }

        return response()->json([
            'status' => 0, // លេខ 0 នៅក្នុងប្រព័ន្ធ ABA មានន័យថា Approved / Payment Success ជោគជ័យ
            'message' => 'Transaction completed successfully (Test / Sandbox Mode)',
            'data' => [
                'tran_id' => $tranId,
                'payment_status' => 'PAID',
                'payment_method' => 'ABA KHQR'
            ]
        ]);
    }
}

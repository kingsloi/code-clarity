<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Scan>
 */
class ScanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $document = [
            'id' => 'abc',
            'document_id' => 'abc',
            'remote_id' => '',
            'file_name' => 'pdfdeskew-com-page-1-10-page1.pdf',
            'media_link' => 'https://api.docparser.com/v1/document/media/abc',
            'media_link_original' => 'https://api.docparser.com/v1/document/media/abc/original',
            'media_link_data' => 'https://api.docparser.com/v1/document/media/abc/data',
            'page_count' => 1,
            'uploaded_at' => '2022-08-11T18:33:14+00:00',
            'processed_at' => '2022-08-16T18:17:01+00:00',
            'uploaded_at_utc' => '2022-08-11T18:33:14+00:00',
            'uploaded_at_user' => '2022-08-11T10:33:14+00:00',
            'processed_at_utc' => '2022-08-16T18:17:01+00:00',
            'processed_at_user' => '2022-08-16T10:17:01+00:00',
            'visit_id' => '123456789',
            'total_charges' => '20.00',
            'service_date' => '01/01/2021',
            'account_class' => NULL,
            'attending_physician' => 'Bob Ross, MD',
            'total_balance' => '0.00',
            'charges' => [
                0 => [
                    'key_0' => '01/01/21',
                    'key_1' => '1234',
                    'key_2' => '123456',
                    'key_3' => 'Example Service item',
                    'key_4' => 1,
                    'key_5' => 10,
                ],
            ],
            'payments' => [
                0 => [
                    'key_0' => '01/01/21',
                    'key_1' => 'Blue Cross Blue Shield or Anthem INSURANCE PAYMENT',
                    'key_2' => '-5',
                ],
                1 => [
                    'key_0' => '01/01/21',
                    'key_1' => 'Blue Cross Blue Shield or Anthem INSURANCE ADJUSTMENT',
                    'key_2' => '-5',
                ],
            ]
        ];

        return [
            'document' => json_encode($document)
        ];
    }
}

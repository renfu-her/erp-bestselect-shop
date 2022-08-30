<?php

namespace Database\Seeders;

use App\Enums\Supplier\Payment;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportSupplierSeeder extends Seeder
{
    /**
     * 匯入廠商
     * @return void
     */
    public function run()
    {
        $firmData = DB::table('firm')->get();

        foreach ($firmData as $firmDatum) {
            if (DB::table('prd_suppliers')
                ->select('vat_no')
                ->where('vat_no', trim($firmDatum->BusinessID))
                ->doesntExist()) {

                if (trim($firmDatum->Tel) === "-") {
                    $contactTel = "";
                } elseif (trim($firmDatum->TelArea) === "") {
                    $contactTel = trim($firmDatum->Tel);
                } else {
                    $contactTel = trim($firmDatum->TelArea) . '-' . trim($firmDatum->Tel);
                }

                if (trim($firmDatum->Fax) === "-") {
                    $fax = "";
                } elseif (trim($firmDatum->TelArea) === "") {
                    $fax = trim($firmDatum->Fax);
                } else {
                    $fax = trim($firmDatum->TelArea) . '-' . trim($firmDatum->Fax);
                }

                print_r($firmDatum->ID . ':' . $firmDatum->Name);
                $supplierId = Supplier::create([
                    'name'                 => $firmDatum->Name,
                    'nickname'             => $firmDatum->SimName,
                    'vat_no'               => (trim($firmDatum->BusinessID) === "") ? 'NIL' : trim($firmDatum->BusinessID),
                    'postal_code'          => '',
                    'contact_tel'          => $contactTel,
                    'contact_address'      => $firmDatum->Address,
                    'contact_person'       => $firmDatum->ContactPerson,
                    'job'                  => '',
                    'extension'            => '',
                    'fax'                  => $fax,
                    'mobile_line'          => '',
                    'email'                => $firmDatum->Email,
                    'invoice_address'      => $firmDatum->InvoiceAddress,
                    'invoice_postal_code'  => null,
                    'invoice_recipient'    => null,
                    'invoice_email'        => null,
                    'invoice_phone'        => null,
                    'invoice_date'         => null,
                    'invoice_ship_fk'      => 1,
                    'invoice_date_fk'      => 1,
                    'shipping_address'     => null,
                    'shipping_postal_code' => null,
                    'shipping_recipient'   => null,
                    'shipping_phone'       => null,
                    'shipping_method_fk'   => 1,
                    'pay_date'             => null,
                    'account_fk'           => 1,
                    'account_date'         => 1,
                    'request_data'         => '',
                    'def_paytype'          => ($firmDatum->BankAccounts !== '') ? Payment::Remittance : Payment::Cash,
                    'memo'                 => $firmDatum->Remarks,
                ])->id;

                if ($firmDatum->BankAccounts !== '') {
                    SupplierPayment::create([
                        'supplier_id' => $supplierId,
                        'type'        => Payment::Remittance,
                        'bank_cname'  => $firmDatum->BankName,
                        'bank_code'   => '',
                        'bank_acount' => $firmDatum->AccountsName,
                        'bank_numer'  => $firmDatum->BankAccounts,
                    ]);
                } else {
                    SupplierPayment::create([
                        'supplier_id' => $supplierId,
                        'type'        => Payment::Cash,
                    ]);
                }
            }
        }

    }
}

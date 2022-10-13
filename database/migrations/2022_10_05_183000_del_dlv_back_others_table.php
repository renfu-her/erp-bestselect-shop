<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DelDlvBackOthersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('dlv_back_others');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::select("CREATE TABLE `dlv_back_others` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '退貨物流id ',
          `grade_id` bigint unsigned NOT NULL COMMENT '會計科目id',
          `delivery_id` bigint unsigned NOT NULL COMMENT '出貨單id',
          `type` tinyint NOT NULL COMMENT '類別 1:物流 2:銷貨收入',
          `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '名稱',
          `price` decimal(12,2) DEFAULT NULL COMMENT '金額',
          `qty` int DEFAULT NULL COMMENT '數量',
          `memo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '備註',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}

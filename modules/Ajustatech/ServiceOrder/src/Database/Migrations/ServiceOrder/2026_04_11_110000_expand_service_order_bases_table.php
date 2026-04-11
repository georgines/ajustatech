<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_order_bases', function (Blueprint $table) {
            $table->uuid('customer_id')->nullable()->after('status_flow_id');
            $table->uuid('equipment_type_id')->nullable()->after('customer_id');
            $table->uuid('selected_document_id')->nullable()->after('equipment_type_id');
            $table->json('customer_snapshot_json')->nullable()->after('selected_document_id');
            $table->string('equipment_brand', 120)->nullable()->after('customer_snapshot_json');
            $table->string('equipment_model', 120)->nullable()->after('equipment_brand');
            $table->string('equipment_serial_number', 120)->nullable()->after('equipment_model');
            $table->timestamp('opened_at')->nullable()->after('equipment_serial_number');
            $table->timestamp('finished_at')->nullable()->after('opened_at');

            $table->index('customer_id', 'sob_customer_idx');
            $table->index('equipment_type_id', 'sob_eq_type_idx');
            $table->index('selected_document_id', 'sob_sel_doc_idx');
            $table->index('opened_at', 'sob_opened_at_idx');
            $table->index('finished_at', 'sob_finished_at_idx');

            $table->foreign('customer_id', 'sob_customer_fk')
                ->references('id')
                ->on('customers')
                ->restrictOnDelete();

            $table->foreign('equipment_type_id', 'sob_eq_type_fk')
                ->references('id')
                ->on('service_order_equipment_types')
                ->nullOnDelete();

            $table->foreign('selected_document_id', 'sob_sel_doc_fk')
                ->references('id')
                ->on('service_order_equipment_type_documents')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_order_bases', function (Blueprint $table) {
            $table->dropForeign('sob_customer_fk');
            $table->dropForeign('sob_eq_type_fk');
            $table->dropForeign('sob_sel_doc_fk');

            $table->dropIndex('sob_customer_idx');
            $table->dropIndex('sob_eq_type_idx');
            $table->dropIndex('sob_sel_doc_idx');
            $table->dropIndex('sob_opened_at_idx');
            $table->dropIndex('sob_finished_at_idx');

            $table->dropColumn([
                'customer_id',
                'equipment_type_id',
                'selected_document_id',
                'customer_snapshot_json',
                'equipment_brand',
                'equipment_model',
                'equipment_serial_number',
                'opened_at',
                'finished_at',
            ]);
        });
    }
};

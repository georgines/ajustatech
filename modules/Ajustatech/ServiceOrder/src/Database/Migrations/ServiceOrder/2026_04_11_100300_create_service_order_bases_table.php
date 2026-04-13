<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_bases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('order_number')->nullable();
            $table->uuid('status_flow_id')->nullable();
            $table->uuid('customer_id')->nullable();
            $table->uuid('equipment_type_id')->nullable();
            $table->uuid('selected_document_id')->nullable();
            $table->json('customer_snapshot_json')->nullable();
            $table->string('equipment_brand', 120)->nullable();
            $table->string('equipment_model', 120)->nullable();
            $table->string('equipment_serial_number', 120)->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->unique('order_number', 'sob_order_number_uidx');
            $table->index('status_flow_id', 'sob_status_flow_idx');
            $table->index('customer_id', 'sob_customer_idx');
            $table->index('equipment_type_id', 'sob_eq_type_idx');
            $table->index('selected_document_id', 'sob_sel_doc_idx');
            $table->index('opened_at', 'sob_opened_at_idx');
            $table->index('finished_at', 'sob_finished_at_idx');

            $table->foreign('status_flow_id', 'sob_status_flow_fk')
                ->references('id')
                ->on('service_order_status_flows')
                ->nullOnDelete();

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
        Schema::dropIfExists('service_order_bases');
    }
};

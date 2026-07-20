<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToBooksTable extends Migration
{
    public function up()
    {
        Schema::table('books', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('id');
            $table->string('edition')->nullable()->after('publisher');
            $table->year('publication_year')->nullable()->after('edition');
            $table->string('language', 50)->default('English')->after('publication_year');
            $table->string('barcode')->nullable()->unique()->after('isbn');
            $table->integer('available_qty')->default(0)->after('qty');
            $table->decimal('price', 10, 2)->nullable()->after('available_qty');
            $table->string('shelf_location', 100)->nullable()->after('price');
            $table->text('description')->nullable()->after('shelf_location');
            $table->string('cover_image')->nullable()->after('description');
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('book_categories')->onDelete('set null');
            $table->index('category_id');
            $table->index('isbn');
            $table->index('barcode');
        });
    }

    public function down()
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'category_id', 'edition', 'publication_year', 'language',
                'barcode', 'available_qty', 'price', 'shelf_location',
                'description', 'cover_image', 'deleted_at',
            ]);
        });
    }
}

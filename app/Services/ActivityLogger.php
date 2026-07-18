<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * Catat aktivitas ke tabel activity_logs.
     *
     * Method ini otomatis mengambil user yang sedang login (auth()->id())
     * dan menyimpan record baru ke tabel activity_logs.
     *
     * Contoh pemanggilan:
     *
     *     // Saat create produk
     *     ActivityLogger::log('create', $product, null, $product->toArray());
     *
     *     // Saat update produk
     *     ActivityLogger::log('update', $product, $original, $product->toArray());
     *
     *     // Saat delete produk
     *     ActivityLogger::log('delete', $product, $product->toArray(), null);
     *
     *     // Stock opname
     *     ActivityLogger::log('stock_opname', $product, null, $product->toArray());
     *
     * @param  string  $action     'create' | 'update' | 'delete' | 'stock_opname'
     * @param  Model   $model      Instance model yang diubah
     * @param  array|null $oldValues  Data sebelum perubahan (null untuk create)
     * @param  array|null $newValues  Data sesudah perubahan (null untuk delete)
     * @return ActivityLog
     */
    public static function log(
        string $action,
        Model $model,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): ActivityLog {
        return ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'model_type'  => class_basename($model),
            'model_id'    => $model->getKey(),
            'description' => static::generateDescription($action, $model, $newValues),
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
        ]);
    }

    /**
     * Generate deskripsi otomatis berdasarkan aksi dan model.
     */
    protected static function generateDescription(string $action, Model $model, ?array $newValues): string
    {
        $modelName = class_basename($model);
        $label = $newValues['name']
            ?? $newValues['invoice_number']
            ?? $newValues['sku']
            ?? "#{$model->getKey()}";

        return match ($action) {
            'create'       => "{$modelName} {$label} berhasil ditambahkan.",
            'update'       => "{$modelName} {$label} berhasil diperbarui.",
            'delete'       => "{$modelName} {$label} berhasil dihapus.",
            'stock_opname' => "Stock opname {$modelName} #{$model->getKey()}.",
            default        => "{$modelName} {$label}: {$action}.",
        };
    }
}
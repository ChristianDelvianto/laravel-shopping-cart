<?php

namespace App\Jobs;

use App\Mail\ProductStockAvailable;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class NotifyProductStockAvailability implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Product $product
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Product's version incremented when stock available
        $productVersion = $this->product->version - 1;

        User::query()
        ->select('users.name', 'users.email')
        ->join('product_notify', 'users.id', '=', 'product_notify.user_id')
        ->where('product_notify.product_id', $this->product->id)
        ->where('product_notify.product_version', $productVersion)
        ->orderBy('product_notify.created_at')
        ->chunk(40, function ($users) {
            foreach ($users as $user) {
                Mail::to($user->email)
                ->send(new ProductStockAvailable($this->product, $user->name));
            }
        });
    }
}

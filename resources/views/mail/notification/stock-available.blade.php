<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Stock available</title>
    </head>
    <body>
        <div>
            Hi {{ $user->email }}
        </div>

        <div>
            Sold products:

            @foreach ($soldProducts as $product)
                <div>{{ $product->name }},
                    <br />
                    amount: {{ $product->total_quantity }}
                    <br />
                    total sales: {{ $product->total_sales }}
                </div>
            @endforeach
        </div>
    </body>
</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ url('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ url('assets/vendor/css/theme-default.css') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Midtrans Payment Gateaway</title>
</head>

<body>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/noty/3.1.4/noty.min.js"
        integrity="sha512-lOrm9FgT1LKOJRUXF3tp6QaMorJftUjowOWiDcG5GFZ/q7ukof19V0HKx/GWzXCdt9zYju3/KhBNdCLzK8b90Q=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@mojs/core"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
        crossorigin="anonymous"></script>
    <script>
        snap.pay(@json($snapToken), {
            // Optional
            onSuccess: function(result) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                            .attr('content')
                    }
                });
                $.ajax({
                    url: "/cst",
                    method: "POST",
                    data: {
                        id: @json($id),
                        status: 'success',
                        result: result
                    },
                    success: function(data) {
                        window.location = '/refresh';
                    }
                });
            },
            // Optional
            onPending: function(result) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                            .attr('content')
                    }
                });
                $.ajax({
                    url: "/cst",
                    method: "POST",
                    data: {
                        id: @json($id),
                        status: 'warning',
                        result: result
                    },
                    success: function(data) {
                        window.location = '/refresh';
                    }
                });
            },
            // Optional
            onError: function(result) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                            .attr('content')
                    }
                });
                $.ajax({
                    url: "/cst",
                    method: "POST",
                    data: {
                        id: @json($id),
                        status: 'error',
                        result: result
                    },
                    success: function(data) {
                        window.location = '/refresh';
                    }
                });
            }
        });
    </script>


</body>

</html>

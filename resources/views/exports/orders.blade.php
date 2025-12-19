<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>User Orders</title>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Service Name</th>
                <th>Price</th>
                <th>Points</th>
                <th>Duration</th>
                <th>Date Orders</th>
                <th>Time Orders</th>
                <th>Notes</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td>{{ $order->user->name }}</td>
                    <td>{{ $order->user->email }}</td>
                    <td>{{ $order->spa_service->name }}</td>
                    <td>{{ $order->spa_service->price }}</td>
                    <td>{{ $order->spa_service->points }}</td>
                    <td>{{ $order->spa_service->duration }}</td>
                    <td>{{ $order->date_service }}</td>
                    <td>{{ $order->time_service }}</td>
                    <td>{{ $order->notes }}</td>
                    <td>{{ $order->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

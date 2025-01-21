<table>
    <thead>
        <tr>
            <th>Provider ID</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Salary</th>
            <th>Commission Type</th>
            <th>Commission Amount</th>
            <th>Earning</th>
            <th>Received</th>
            <th>Outstanding</th>
            <th>Last Payment</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($providers as $provider)
        <tr>
            <td>{{ $provider['provider_id'] }}</td>
            <td>{{ $provider['first_name'] }} {{ $provider['last_name'] }}</td>
            <td>{{ $provider['phone'] }}</td>
            <td>{{ $provider['salary'] }}</td>
            <td>{{ $provider['commission']['type'] }}</td>
            <td>{{ $provider['commission']['amount'] }}</td>
            <td>{{ $provider['earning']['total'] }}</td>
            <td>{{ $provider['received'] }}</td>
            <td>{{ $provider['outstanding'] }}</td>
            <td>{{ $provider['last_payment']['amount'] ?? '' }}</td>
            <td>{{ $provider['last_payment']['created_at'] ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

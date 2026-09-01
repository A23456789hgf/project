@props([
    'headers' => [],
    'striped' => true,
    'hoverable' => true,
])

<div class="ds-table-responsive">
    <table {{ $attributes->merge(['class' => 'ds-table' . ($striped ? ' table-striped' : '') . ($hoverable ? ' table-hover' : '')]) }}>
        @if(!empty($headers))
            <thead>
                <tr>
                    @foreach($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>

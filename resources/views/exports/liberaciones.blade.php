@php
    $cols = collect($columns);
    $rows = collect($rows ?? []);
@endphp
<table>
    <tr>
        <td colspan="{{ $cols->count()+1 }}">
            @if(file_exists($logoPath))
                <img src="{{ $logoPath }}" alt="logo" width="180">
            @else
                <strong>Banco – Listado de Liberaciones</strong>
            @endif
        </td>
    </tr>
    <tr><td colspan="{{ $cols->count()+1 }}"><strong>Listado de Liberaciones</strong></td></tr>
    <tr><td colspan="{{ $cols->count()+1 }}">Estatus por fila: Pendiente / Liberado</td></tr>
    <tr></tr>
    <tr>
        @foreach($cols as $c)
            <th>{{ $c['label'] }}</th>
        @endforeach
        <th>Estatus</th>
    </tr>
    @foreach($rows as $r)
        <tr>
            @foreach($cols as $c)
                <td>{{ $r['values'][$c['id']] ?? '' }}</td>
            @endforeach
            <td>{{ strtoupper($r['status'] ?? 'pendiente') }}</td>
        </tr>
    @endforeach
</table>

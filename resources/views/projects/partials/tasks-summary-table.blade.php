{{-- resources/views/components/tasks-summary-table.blade.php --}}
<div class="overflow-x-auto rounded-lg shadow mt-8">
    <table class="min-w-full bg-white">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="py-3 px-4 text-start">Entity Name</th>
                <th class="py-3 px-4 text-start">Type</th>
                <th class="py-3 px-4 text-start">Task</th>
                <th class="py-3 px-4 text-center">Team Members</th>
                <th class="py-3 px-4 text-start">Status</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            @foreach($summaries as $summary)
                @php
                    $statusColor = $summary->assigned_team_count > 0 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                @endphp
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="py-3 px-4">
                        {{ $summary->entity_name }}
                        @if($summary->entity->is_sub_entity)
                            <span class="text-xs text-blue-600 ms-1">(Sub)</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">{{ $summary->entity->entity_type }}</td>
                    <td class="py-3 px-4">{{ $summary->entity_task }}</td>
                    <td class="py-3 px-4 text-center">
                        <span class="inline-block w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center">
                            {{ $summary->assigned_team_count }}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <span class="px-3 py-1 rounded-full text-xs {{ $statusColor }}">
                            {{ $summary->assigned_team_count > 0 ? 'Assigned' : 'Pending' }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@php
    $rows = [
        [[$typeConfig['author_name_label'], $review->student_name], ['Študijný program', $review->study_program]],
        [['Názov práce', $review->thesis_title, 'title-box']],
        [['Meno vedúceho', $review->supervisor_name], $isOpponent ? ['Meno oponenta', $review->opponent_name] : null],
    ];
@endphp
<div class="top-fields">
    @foreach ($rows as $row)
        <table class="field-table">
            <tr>
                @foreach ($row as $index => $cell)
                    @if ($index > 0)
                        <td class="field-gap"></td>
                    @endif
                    <td class="{{ count($row) === 1 ? 'field-cell-full' : 'field-cell' }}">
                        @if ($cell)
                            <div class="field-label">{{ $cell[0] }}:</div>
                            <div class="field-box {{ $cell[2] ?? '' }}">{{ $cell[1] }}</div>
                        @endif
                    </td>
                @endforeach
            </tr>
        </table>
    @endforeach
</div>
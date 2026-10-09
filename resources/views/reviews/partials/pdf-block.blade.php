<div class="section">
    <div class="section-title">{{ str_replace('{author}', $typeConfig['author_genitive'], $block['title']) }}</div>
    <table class="matrix">
        <thead>
            <tr>
                <th class="crit-col"></th>
                @foreach ($grades as $code => $label)
                    <th class="grade-col"><span class="grade-code">{{ $code }}</span><span class="grade-name">{{ $gradeLabels[$code] }}</span></th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($block['criteria'] as $field => $label)
                <tr>
                    <td class="crit-col">{{ \App\Support\CriterionLabel::resolve($label, $type, $review->review_role) }}:</td>
                    @foreach ($grades as $code => $gradeLabel)
                        <td class="grade-col"><span class="tick">{{ $review->{$field} === $code ? 'X' : '' }}</span></td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="comment-block {{ $blockName === 'quality' && $isOpponent ? 'large' : 'small' }}"><span class="comment-label">Komentár:</span>
{{ $review->{$block['comment']} }}</div>
</div>
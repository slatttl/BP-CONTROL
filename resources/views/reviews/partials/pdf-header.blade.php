<div class="top-fields">
    <div class="label-row">
        <div class="half field-label">{{ $typeConfig['author_name_label'] }}:</div>
        <div class="half right field-label">Študijný program:</div>
    </div>
    <div class="value-row">
        <div class="half field-box">{{ $review->student_name }}</div>
        <div class="half right field-box">{{ $review->study_program }}</div>
    </div>

    <div class="label-row">
        <div class="full field-label">Názov práce:</div>
    </div>
    <div class="value-row">
        <div class="full field-box title-box">{{ $review->thesis_title }}</div>
    </div>

    <div class="label-row">
        <div class="half field-label">Meno vedúceho:</div>
        @if ($isOpponent)
            <div class="half right field-label">Meno oponenta:</div>
        @endif
    </div>
    <div class="value-row">
        <div class="half field-box">{{ $review->supervisor_name }}</div>
        @if ($isOpponent)
            <div class="half right field-box">{{ $review->opponent_name }}</div>
        @endif
    </div>
    <div class="clearfix"></div>
</div>
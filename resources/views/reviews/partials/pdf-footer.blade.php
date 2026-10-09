<div class="footer-row">
    <div class="footer-left">Miesto a dátum: {{ $review->place }}, {{ $review->review_date?->format('j.n.Y') }}</div>
    <div class="footer-right"><div class="signature-name">{{ $signer }}</div></div>
    <div class="clearfix"></div>
</div>
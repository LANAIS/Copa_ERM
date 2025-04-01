@php
    $targetDate = App\Models\CountdownConfig::getTargetDate();
@endphp

<div class="countdown" data-target-date="{{ $targetDate }}">
    <div class="countdown-item">
        <span class="countdown-number days">00</span>
        <span class="countdown-label">Días</span>
    </div>
    <div class="countdown-item">
        <span class="countdown-number hours">00</span>
        <span class="countdown-label">Horas</span>
    </div>
    <div class="countdown-item">
        <span class="countdown-number minutes">00</span>
        <span class="countdown-label">Minutos</span>
    </div>
    <div class="countdown-item">
        <span class="countdown-number seconds">00</span>
        <span class="countdown-label">Segundos</span>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const countdown = document.querySelector('.countdown');
    const targetDate = new Date(countdown.dataset.targetDate).getTime();

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = targetDate - now;

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        countdown.querySelector('.days').textContent = String(days).padStart(2, '0');
        countdown.querySelector('.hours').textContent = String(hours).padStart(2, '0');
        countdown.querySelector('.minutes').textContent = String(minutes).padStart(2, '0');
        countdown.querySelector('.seconds').textContent = String(seconds).padStart(2, '0');

        if (distance < 0) {
            clearInterval(countdownInterval);
            countdown.innerHTML = '<div class="countdown-expired">¡El evento ha comenzado!</div>';
        }
    }

    updateCountdown();
    const countdownInterval = setInterval(updateCountdown, 1000);
});
</script>
@endpush 
document.addEventListener('DOMContentLoaded', () => {
    const event = document.querySelector('[data-event-date]');

    if (!event) return;

    const target = new Date(event.dataset.eventDate).getTime();
    const fields = Object.fromEntries([...document.querySelectorAll('[data-countdown]')]
        .map((field) => [field.dataset.countdown, field]));

    const updateCountdown = () => {
        const distance = Math.max(0, target - Date.now());
        const days = Math.floor(distance / 86_400_000);
        const hours = Math.floor((distance % 86_400_000) / 3_600_000);
        const minutes = Math.floor((distance % 3_600_000) / 60_000);

        if (fields.days) fields.days.textContent = String(days).padStart(2, '0');
        if (fields.hours) fields.hours.textContent = String(hours).padStart(2, '0');
        if (fields.minutes) fields.minutes.textContent = String(minutes).padStart(2, '0');
    };

    updateCountdown();
    window.setInterval(updateCountdown, 60_000);
});

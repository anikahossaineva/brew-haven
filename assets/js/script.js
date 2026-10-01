document.addEventListener("DOMContentLoaded", () => {
    const newsletterForm = document.getElementById("newsletter-form");
    if (!newsletterForm) return;

    newsletterForm.addEventListener("submit", async (event) => {
        event.preventDefault();
        const result = document.getElementById("newsletter-result");
        const button = newsletterForm.querySelector("button[type='submit']");
        button.disabled = true;
        try {
            const response = await fetch("api/newsletter.php", { method: "POST", body: new FormData(newsletterForm) });
            const data = await response.json();
            result.textContent = data.message || "Subscription could not be saved.";
        } catch {
            result.textContent = "Connection issue. Please try again.";
        } finally {
            button.disabled = false;
        }
    });
});

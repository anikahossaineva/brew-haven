<?php
include 'includes/header.php';
?>

<section class="py-5">

<div class="container">

<div class="text-center mb-5">

<h1 class="fw-bold">
😊 Mood Match
</h1>

<p class="text-muted">
Tell us how you're feeling and we'll recommend the perfect Brew Haven meal.
</p>

<div class="recommendation-progress mb-5">

    <div class="progress-header">
        <span>Recommendation Progress</span>
        <span id="progressText">Step 1 of 5</span>
    </div>

    <div class="progress" style="height:8px;">

        <div class="progress-bar bg-dark"
             id="progressBar"
             style="width:20%;">
        </div>

    </div>

</div>

</div>

<form>

<div class="row g-4">

<div class="col-12">

<label class="form-label fw-bold mb-3">
    😊 How are you feeling today?
</label>

<div class="row g-3">

    <div class="col-md-4 col-6">
        <div class="mood-card" data-value="Energetic">
            <span class="emoji">😊</span>
            <h6>Energetic</h6>
        </div>
    </div>

    <div class="col-md-4 col-6">
        <div class="mood-card" data-value="Sleepy">
            <span class="emoji">😴</span>
            <h6>Sleepy</h6>
        </div>
    </div>

    <div class="col-md-4 col-6">
        <div class="mood-card" data-value="Relaxed">
            <span class="emoji">😌</span>
            <h6>Relaxed</h6>
        </div>
    </div>

    <div class="col-md-4 col-6">
        <div class="mood-card" data-value="Need a Boost">
            <span class="emoji">🔥</span>
            <h6>Need a Boost</h6>
        </div>
    </div>

    <div class="col-md-4 col-6">
        <div class="mood-card" data-value="Very Hungry">
            <span class="emoji">🤤</span>
            <h6>Very Hungry</h6>
        </div>
    </div>

    <div class="col-md-4 col-6">
        <div class="mood-card" data-value="Low-key">
            <span class="emoji">🌙</span>
            <h6>Low-key</h6>
        </div>
    </div>

</div>

<input type="hidden" name="mood" id="selectedMood">

</div>

<div class="col-md-6">

<label class="form-label">

Time of Day

</label>

<select class="form-select">

<option>Morning</option>

<option>Afternoon</option>

<option>Evening</option>

<option>Night</option>

</select>

</div>

<div class="col-md-6">

<label class="form-label">

Budget

</label>

<select class="form-select">

<option>Under ৳300</option>

<option>৳300 - ৳500</option>

<option>Above ৳500</option>

</select>

</div>

<div class="col-md-6">

<label class="form-label">

Preferred Drink

</label>

<select class="form-select">

<option>Coffee</option>

<option>Tea</option>

<option>Juice</option>

<option>No Preference</option>

</select>

</div>

<div class="col-md-12">

<label class="form-label">

What are you craving?

</label>

<select class="form-select">

<option>Breakfast</option>

<option>Burger</option>

<option>Pasta</option>

<option>Dessert</option>

<option>Anything</option>

</select>

</div>

<div class="text-center mt-5">

<button class="btn btn-dark btn-lg">

Find My Perfect Match

</button>

</div>

</div>

</form>

</div>

</section>

<script>
const moodCards = document.querySelectorAll(".mood-card");
const selectedMood = document.getElementById("selectedMood");

moodCards.forEach(card => {

    card.addEventListener("click", function(){

        moodCards.forEach(c => c.classList.remove("active"));

        this.classList.add("active");

        selectedMood.value = this.dataset.value;

    });

});
</script>

<?php
include 'includes/footer.php';
?>
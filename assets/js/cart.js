document.addEventListener("DOMContentLoaded",()=>{

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || "";

// =============================
// Add To Cart
// =============================

document.querySelectorAll(".add-cart-btn").forEach(btn=>{

btn.addEventListener("click",()=>{

fetch("api/add_to_cart.php",{

method:"POST",

headers:{"Content-Type":"application/x-www-form-urlencoded"},

body:new URLSearchParams({product_id:btn.dataset.id,csrf_token:csrfToken})

})

.then(res=>res.json())

.then(data=>{

if(data.success){

showToast(data.message);

document.querySelector(".cart-count").innerText=data.cart_count;

}else{

window.location="login.php";

}

});

});

});

// =============================
// Increase Quantity
// =============================

document.querySelectorAll(".increase-btn").forEach(btn=>{

btn.addEventListener("click",()=>{

updateCart(btn.dataset.id,"increase");

});

});

// =============================
// Decrease Quantity
// =============================

document.querySelectorAll(".decrease-btn").forEach(btn=>{

btn.addEventListener("click",()=>{

updateCart(btn.dataset.id,"decrease");

});

});

// =============================
// Remove
// =============================

document.querySelectorAll(".remove-btn").forEach(btn=>{

btn.addEventListener("click",()=>{

fetch("api/remove_cart.php",{

method:"POST",

headers:{"Content-Type":"application/x-www-form-urlencoded"},

body:new URLSearchParams({cart_id:btn.dataset.id,csrf_token:csrfToken})

})

.then(()=>{

location.reload();

});

});

});

});

// =============================
// Update Cart
// =============================

function updateCart(id, action) {

    fetch("api/update_cart.php", {

        method: "POST",

        headers: {"Content-Type": "application/x-www-form-urlencoded"},

        body: new URLSearchParams({cart_id:id,action,csrf_token:csrfToken})

    })

    .then(res => res.json())

    .then(data => {

        if (!data.success) return;

        location.reload();

    });

}

// =============================
// Toast
// =============================

function showToast(message){

const toast=document.createElement("div");

toast.className="custom-toast";

toast.innerHTML="✅ "+message;

document.body.appendChild(toast);

setTimeout(()=>{

toast.classList.add("show");

},100);

setTimeout(()=>{

toast.remove();

},3000);

}

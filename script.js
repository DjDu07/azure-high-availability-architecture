// ====== Referencias del DOM ======
const carritoContenido = document.getElementById("carrito-contenido");
const carritoTotal = document.getElementById("carrito-total");
const btnVaciar = document.getElementById("btn-vaciar");
const btnCheckout = document.getElementById("btn-checkout");
const cartCount = document.getElementById("cart-count");
const formPago = document.getElementById("form-pago");
const cartInput = document.getElementById("cart-data");

let carrito = [];

// ====== Utilidades ======
function formatearMoneda(monto) {
  return "S/ " + monto.toFixed(2);
}

// ====== Renderizado del carrito ======
function renderCarrito() {
  carritoContenido.innerHTML = "";

  if (carrito.length === 0) {
    carritoContenido.innerHTML =
      '<p class="carrito-vacio">Tu carrito está vacío.</p>';
    carritoTotal.textContent = "S/ 0.00";
    btnVaciar.disabled = true;
    btnCheckout.disabled = true;
    cartCount.textContent = "0";
    return;
  }

  let total = 0;
  let totalItems = 0;

  carrito.forEach((item) => {
    const subtotal = item.precio * item.cantidad;
    total += subtotal;
    totalItems += item.cantidad;

    const div = document.createElement("div");
    div.className = "carrito-item";
    div.innerHTML = `
      <div class="carrito-item-info">
        <span class="carrito-item-nombre">${item.nombre}</span>
        <span class="carrito-item-detalle">
          ${item.cantidad} x ${formatearMoneda(item.precio)}
        </span>
      </div>
      <div class="carrito-item-subtotal">
        ${formatearMoneda(subtotal)}
      </div>
    `;
    carritoContenido.appendChild(div);
  });

  carritoTotal.textContent = formatearMoneda(total);
  btnVaciar.disabled = false;
  btnCheckout.disabled = false;
  cartCount.textContent = String(totalItems);
}

// ====== Lógica para añadir productos ======
function agregarAlCarrito(productoElemento) {
  const id = productoElemento.dataset.id;
  const nombre = productoElemento.dataset.nombre;
  const precio = parseFloat(productoElemento.dataset.precio);
  const inputCantidad = productoElemento.querySelector(".cantidad");
  const cantidad = parseInt(inputCantidad.value, 10) || 1;

  if (cantidad <= 0) return;

  const existente = carrito.find((item) => item.id === id);
  if (existente) {
    existente.cantidad += cantidad;
  } else {
    carrito.push({ id, nombre, precio, cantidad });
  }

  renderCarrito();
  inputCantidad.value = 1;
}

// Eventos para botones "Añadir"
document.querySelectorAll(".btn-agregar").forEach((boton) => {
  boton.addEventListener("click", () => {
    const producto = boton.closest(".producto");
    agregarAlCarrito(producto);
  });
});

// Vaciar carrito
btnVaciar.addEventListener("click", () => {
  carrito = [];
  renderCarrito();
});

// Ir a checkout
btnCheckout.addEventListener("click", () => {
  document.getElementById("checkout").scrollIntoView({ behavior: "smooth" });
});

// ====== Envío del formulario al backend ======
formPago.addEventListener("submit", (e) => {
  if (carrito.length === 0) {
    e.preventDefault();
    alert(
      "Tu carrito está vacío. Añade al menos un producto antes de confirmar el pedido."
    );
    return;
  }

  // Guardamos el carrito en JSON en el campo hidden
  cartInput.value = JSON.stringify(carrito);
  // NO se hace preventDefault: se envía el form a procesar_pedido.php
});

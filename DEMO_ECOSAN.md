# Demo ECOSAN — ficha para la presentación comercial

Landing de demostración para **Escuela de Manejo y Examinadora ECOSAN** (Santa Ana, El Salvador),
construida sobre la base del landing ESDRIVE. Vive en la rama `demo-ecosan` (creada desde `main`,
que conserva la landing ESDRIVE intacta).

- Probar: `http://localhost/EscuelaDeManejo/` con la rama `demo-ecosan` activa.
- Alternar: `git checkout main` (ESDRIVE) / `git checkout demo-ecosan` (ECOSAN demo).

## Datos reales utilizados

Fuente: sitio público **autoescuelaecosan.com** (consultado el 06-nov-2026).

- Nombre y descriptor: "Escuela de Manejo y Examinadora", autorizada por el **VMT**.
- Teléfono **2440-4057** · WhatsApp **7528-8584** (`wa.me/50375288584`) · correo **autoescuelaecosan@gmail.com**
- Dirección: **6a Av. Sur y 21 Calle Pte., Barrio Nuevo, Santa Ana**
- Horario: Lun–Vie 8:00–18:00 · Sáb 8:00–12:00
- Cifras: +5,000 alumnos formados · 97% aprobación · 15+ años
- Catálogo y precios (Particular $180–$200 / Intermedio $160–$170 / Avanzado $140 · Liviano $250/$175 ·
  Motocicleta $120–$160 · Refuerzo por clase · Planes Empresariales con cotización)
- Duración de cursos (31 / 25 / 17 clases; 1 a 6 semanas según horario) y requisitos VMT por edad
- Proceso de inscripción en 4 pasos, métodos de pago (Wompi, Pagadito, Visa, Mastercard, Banco Agrícola)
- Preguntas frecuentes y beneficios (equivalencias las de su sitio)
- Testimonios de María Rodríguez, José Castro y Ana Lima
- Recursos enlazados (no re-alojados): PDFs de guía y manual, simulador VMT, videos de YouTube
- Facebook: `facebook.com/autoescuela.ecosan/` · Simulador: `examen-teorico-ecosan-vmt.netlify.app`

## Placeholders — reemplazar antes de publicar

1. **Fotografías**: todas son stock (Pexels / biblioteca del proyecto). Reemplazar por fotos reales de la
   escuela: flota, instructores, instalaciones, circuito y prácticas. Buscar comentarios `TODO(foto)`.
2. **Logo**: SVG original tipo volante del proyecto; NO es el logo oficial de ECOSAN.
3. **Testimonios**: copiados textualmente de su sitio público. Confirmar permiso y vigencia con el cliente.
4. **Enlaces a políticas/términos**: apuntan al sitio actual
   (`/politicas-de-privacidad`, `/terminos-y-condiciones`). Crear las páginas o apuntar definitivamente allí.
5. **PDFs de recursos**: enlazados al dominio actual; auto-alojarlos o confirmar su permanencia.
6. **Formulario**: envía por **WhatsApp con datos precargados** (sin backend). Fase 2 real: backend +
   Wompi/Pagadito con confirmación automática por correo, hCaptcha y pago en línea estilo su sitio actual.

## Pendiente de confirmación con el cliente

- **Refuerzo**: la tabla de su sitio es ambigua. Se muestra Particular $20 automático / $15 estándar y
  Liviana $30. Confirmar.
- **Liviano Intermedio**: no publican precio; el cotizador del formulario muestra "A consultar con un asesor".
- Teléfonos a usar como CTA principal (WhatsApp vs. teléfono fijo) y vigencia de horarios.
- Redes: solo Facebook público; confirmar si hay Instagram/TikTok oficiales.

## Mejoras frente a su sitio actual (ángulo del pitch)

- Hero con imagen WebP de **63 KB** (su sitio carga imágenes pesadas sin optimizar) y LCP rápido.
- Diseño responsive real probado, sin errores de consola, `prefers-reduced-motion` respetado.
- **Cotizador en vivo**: selecciona curso + nivel y muestra el precio estimado al instante.
- Flujo de inscripción sin fricción: WhatsApp con mensaje precargado (nombre, teléfono, curso, nivel, horario).
- SEO básico correcto: un solo `h1`, meta description, theme-color, favicon, anclas con `scroll-margin`.
- Accesibilidad: labels, `aria-*`, navegación por teclado, contraste verificado en claro y oscuro.

## Fases sugeridas

1. **Fase 1 (esta demo)**: contenido fidedigno + diseño + flujo WhatsApp. Publicable en 1–2 días con fotos reales.
2. **Fase 2**: backend (PHP + MySQL ya existe: panel admin) + pago en línea Wompi/Pagadito + correo automático.
3. **Fase 3**: self-host de recursos, páginas legales, analytics, dominio propio (ecosan.com o similar).

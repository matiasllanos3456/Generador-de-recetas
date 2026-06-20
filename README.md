# Generador-de-recetas
<img width="500" height="200" alt="Gemini_Generated_Image_cv5dv4cv5dv4cv5d" src="https://github.com/user-attachments/assets/df6a58a2-d7af-4f65-a377-40867b15049d" />
<br>
<h2> 🛠️ Tecnologías utilizadas </h2>
<br>
<p align="left">
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg" width="40" height="40" alt="JavaScript" />
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/nodejs/nodejs-original.svg" width="40" height="40" alt="nodejs" />
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/vuejs/vuejs-original.svg" width="40" height="40" alt="vuejs" />
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" width="40" height="40" alt="php" />
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg" width="40" height="40" alt="mysql" />
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/html5/html5-original.svg" width="40" height="40" alt="html5" />
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/css3/css3-original.svg" width="40" height="40" alt="css3" />
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/vscode/vscode-original.svg" width="40" height="40" alt="vscode" />
</p
<br>
<h2>Descripción</h2>
<br>
<p>
La aplicación web permitirá a los usuarios agregar ingredientes a una especie de carrito, indicando su cantidad de gramos o mililitros; cuando haya al menos un ingrediente, se desbloqueará un botón para comenzar a generar la receta. La página mostrará el resultado de la receta, que tendrá el paso a paso; a un lado tendrá los macronutrientes totales; más abajo aparecerán posibles variaciones de la receta y consejos nutricionales. Una vez visto esto, se podrá dar la opción para guardar la receta en la base de datos. La aplicación utilizará la API de GROQ para proponer recetas a los usuarios por su buen rendimiento y la API de la USDA para buscar ingredientes internacionales, así que se necesitarán estas dos API KEYs para usar la aplicación.
</p>
<h3>Restricciones</h3>
<ul>
<li>Dado que el front-end y el back-end van a estar separados, se iniciarán 2 servidores: Uno en XAMPP para ejecutar los scripts de PHP y gestionar las bases de datos, por lo que la carpeta back-end debe ir dentro de la carpeta “/htdocs” y posteriormente iniciar el servidor apache.</li>
<li>El otro servidor será el de desarrollo en Vite, que se encargará de compilar y mostrar la interfaz en tiempo real. La carpeta front-end puede estar en cualquier parte.</li>
<li>Se tendrá que instalar Pinia (libreria de Vue), vue-router, axios.</li>
<li>Se definieron las políticas de CORS mediante las cabeceras "header" en php para que solo el frontend se pueda comunicar con el backend y este le pueda devolver datos.</li>
</ul>
<h3>¿Como usarlo?</h3>
<p>
Cuando hayas iniciado el servidor con Vite y el servidor Apache con la carpeta backend dentro de htdocs en xampp, entrarás a http://localhost:5173 (ejemplo), verás la pantalla de login, si no tienes cuenta te puedes registrar, una vez dentro de la aplicación podrás ir agregando ingredientes a tu lista utilizando la interfaz principal o la barra de busqueda. Una vez que estés listo le das clic en "Generar receta", Vue enviará una petición por detrás utilizando axios a http://localhost/mi-proyecto/backend/index.php (ejemplo), se generará la receta y esta será retornada a la interfaz. Estando satisfecho con la receta generada puedes darle al botón de guardar para insertarla en la base de datos.
</p>
<h3>Flujo de datos</h3>
<p>Interfaz (Vue) --> GlobalStore (Pinia) --> Backend (php) --> BaseDeDatos(MySQL) y APIS extenas (USDA, mymemory(traductor), Groq)</p>

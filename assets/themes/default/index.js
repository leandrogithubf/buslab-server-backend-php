import './admin.css';

/**
 * This function makes the favicon images available on the build folder, as an
 * loaded asset as the js and css files.
 */
const imagesContext = require.context('./images/icons/', true, /\.(png|jpg|jpeg|gif|ico|svg|webp)$/);
imagesContext.keys().forEach(imagesContext);
/**
 * qr.js — Inicialização global do gerador de QR codes.
 * Expõe window.QrCode para uso em diretivas Alpine.js nos componentes Blade.
 */
import QRCode from 'qrcode';

window.QrCode = QRCode;

/**
 * Renderiza um QR code num elemento <canvas>.
 * @param {HTMLCanvasElement} canvas - referência ao canvas
 * @param {string} url - URL de destino
 * @param {number} size - tamanho em px (padrão 96)
 */
window.renderQrCode = function (canvas, url, size = 96) {
    if (!canvas || !url) return;
    QRCode.toCanvas(canvas, url, {
        width: size,
        margin: 1,
        color: { dark: '#18181b', light: '#ffffff' },
        errorCorrectionLevel: 'M',
    }).catch(console.error);
};

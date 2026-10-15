/**
 * qr.js — Inicialização global do gerador de QR codes.
 * Expõe window.QrCode para uso em diretivas Alpine.js nos componentes Blade.
 */
import QRCode from 'qrcode';

window.QrCode = QRCode;

// Cache do logotipo INOTU para reutilização nos canvas
let inotuLogoImage = null;
function getInotuLogo(src = '/imagens/no-background/INOTU.png') {
    if (!inotuLogoImage) {
        inotuLogoImage = new Image();
        inotuLogoImage.src = src;
    }
    return inotuLogoImage;
}

/**
 * Renderiza um QR code num elemento <canvas> com suporte a opções de personalização e logo central.
 * @param {HTMLCanvasElement} canvas - referência ao canvas
 * @param {string} url - URL de destino
 * @param {number|object} sizeOrOptions - tamanho em px ou objeto com opções completas
 */
window.renderQrCode = function (canvas, url, sizeOrOptions = 96) {
    if (!canvas || !url) return;

    let size = 96;
    let darkColor = '#18181b';
    let lightColor = '#ffffff';
    let correctionLevel = 'H'; // Nível 'H' (30% de redundância) para leitura perfeita com logo
    let margin = 1;
    let incluirLogo = true;
    let logoUrl = '/imagens/no-background/INOTU.png';

    if (typeof sizeOrOptions === 'number') {
        size = sizeOrOptions;
    } else if (typeof sizeOrOptions === 'object') {
        size = sizeOrOptions.size || sizeOrOptions.width || 96;
        darkColor = sizeOrOptions.darkColor || sizeOrOptions.cor_qr || '#18181b';
        lightColor = sizeOrOptions.lightColor || sizeOrOptions.cor_fundo || '#ffffff';
        // Para preservar leitura excelente com logo, usamos sempre nível 'H' ou 'Q' no mínimo
        correctionLevel = sizeOrOptions.correctionLevel || sizeOrOptions.nivel_correcao || 'H';
        if (correctionLevel === 'L' || correctionLevel === 'M') {
            correctionLevel = 'H';
        }
        margin = sizeOrOptions.margin !== undefined ? sizeOrOptions.margin : 1;
        if (sizeOrOptions.incluirLogo !== undefined) {
            incluirLogo = sizeOrOptions.incluirLogo;
        }
        if (sizeOrOptions.logoUrl) {
            logoUrl = sizeOrOptions.logoUrl;
        }
    }

    QRCode.toCanvas(canvas, url, {
        width: size,
        margin: margin,
        color: { dark: darkColor, light: lightColor },
        errorCorrectionLevel: correctionLevel,
    }).then(() => {
        if (!incluirLogo) return;

        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        const img = getInotuLogo(logoUrl);

        const desenharLogo = () => {
            const canvasWidth = canvas.width;
            const canvasHeight = canvas.height;
            // Proporção de 22% a 24% da largura do QR para garantir legibilidade de 100% dos scanners
            const logoSize = Math.round(canvasWidth * 0.23);
            const x = Math.round((canvasWidth - logoSize) / 2);
            const y = Math.round((canvasHeight - logoSize) / 2);
            const badgePadding = Math.max(2, Math.round(logoSize * 0.12));
            const badgeSize = logoSize + (badgePadding * 2);
            const badgeX = x - badgePadding;
            const badgeY = y - badgePadding;
            const radius = Math.round(badgeSize * 0.22);

            // Fundo de protecção branco/limpo atrás do logo com cantos arredondados
            ctx.save();
            ctx.fillStyle = lightColor || '#ffffff';
            ctx.beginPath();
            ctx.roundRect(badgeX, badgeY, badgeSize, badgeSize, radius);
            ctx.fill();

            // Borda sutil no badge do logo
            ctx.strokeStyle = darkColor || '#18181b';
            ctx.lineWidth = 1;
            ctx.stroke();

            // Desenho do Logo INOTU
            ctx.drawImage(img, x, y, logoSize, logoSize);
            ctx.restore();
        };

        if (img.complete && img.naturalWidth !== 0) {
            desenharLogo();
        } else {
            img.onload = desenharLogo;
        }
    }).catch(console.error);
};


import mammoth from 'mammoth';
import * as pdfjsLib from 'pdfjs-dist';

// Configurar worker do PDF.js
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.8.69/pdf.worker.min.mjs';

export function extrairDadosDeDocx(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onload = function(event) {
            const arrayBuffer = event.target.result;
            
            mammoth.extractRawText({ arrayBuffer: arrayBuffer })
                .then(function(result) {
                    const texto = result.value;
                    const dados = parseCroquiData(texto);
                    resolve(dados);
                })
                .catch(function(err) {
                    console.error('Erro ao extrair texto do DOCX:', err);
                    reject(err);
                });
        };
        
        reader.onerror = function() {
            reject(new Error('Erro ao ler o ficheiro'));
        };
        
        reader.readAsArrayBuffer(file);
    });
}

export function extrairDadosDePdf(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();

        reader.onload = async function(event) {
            const arrayBuffer = event.target.result;

            try {
                const loadingTask = pdfjsLib.getDocument({ data: arrayBuffer });
                const pdf = await loadingTask.promise;

                let fullText = '';

                for (let i = 1; i <= pdf.numPages; i++) {
                    const page = await pdf.getPage(i);
                    const textContent = await page.getTextContent();
                    const pageText = textContent.items.map(item => item.str).join(' ');
                    fullText += pageText + '\n';
                }

                const dados = parseCroquiData(fullText);
                resolve(dados);
            } catch (err) {
                console.error('Erro ao extrair texto do PDF:', err);
                reject(err);
            }
        };

        reader.onerror = function() {
            reject(new Error('Erro ao ler o ficheiro'));
        };

        reader.readAsArrayBuffer(file);
    });
}

function parseCroquiData(texto) {
    const dados = {
        titulo: '',
        area: '',
        perimetro: '',
        dimensao: '',
        escala: '',
        tecnico_responsavel: '',
        folha_numero: '',
        data_folha: '',
        ano: '',
        proprietarios: [{ nome: '', bi: '', contacto: '' }],
        coordenadas: []
    };
    
    const linhas = texto.split('\n').map(l => l.trim()).filter(l => l);
    
    // Extrair proprietário
    const nomeMatch = texto.match(/(?:Nome|Requerente|Proprietário)\s*[:]\s*([^\n]+)/i);
    if (nomeMatch) {
        dados.proprietarios[0].nome = nomeMatch[1].trim();
    }
    
    // Extrair BI/NIF
    const biMatch = texto.match(/(?:BI\s*\/?\s*NIF|Bilhete\s*de\s*Identidade)\s*n\.?\s*º\s*:?\s*([A-Z0-9]+)/i);
    if (biMatch) {
        dados.proprietarios[0].bi = biMatch[1].trim();
    }
    
    // Extrair título
    if (dados.proprietarios[0].nome) {
        dados.titulo = `Croqui de Localização de ${dados.proprietarios[0].nome}`;
    }
    
    // Extrair área
    const areaMatch = texto.match(/(?:Área\s*Total|Area)\s*[:]\s*([\d.,]+)\s*m[²2]/i);
    if (areaMatch) {
        dados.area = areaMatch[1].replace(',', '.');
    }
    
    // Extrair perímetro
    const perimetroMatch = texto.match(/(?:Perímetro|Perimetro)\s*[:]\s*([\d.,]+)\s*m/i);
    if (perimetroMatch) {
        dados.perimetro = perimetroMatch[1].replace(',', '.');
    }
    
    // Extrair dimensão
    const dimensaoMatch = texto.match(/(?:Dimensão|Dimensao)\s*[:]\s*([^\n]+)/i);
    if (dimensaoMatch) {
        dados.dimensao = dimensaoMatch[1].trim();
    } else {
        const dimMatch = texto.match(/(\d+[,.]?\d*)\s*m\s*[x×]\s*(\d+[,.]?\d*)\s*m/i);
        if (dimMatch) {
            dados.dimensao = `${dimMatch[1].replace(',', '.')} m x ${dimMatch[2].replace(',', '.')} m`;
        }
    }
    
    // Extrair escala
    const escalaMatch = texto.match(/Escala\s*[:]\s*(1\/\d+)/i);
    if (escalaMatch) {
        dados.escala = escalaMatch[1];
    }
    
    // Extrair técnico responsável
    const tecnicoMatch = texto.match(/(?:Técnico\s*Responsável|Tecnico\s*Responsavel)\s*[:]\s*([^\n]+)/i);
    if (tecnicoMatch) {
        dados.tecnico_responsavel = tecnicoMatch[1].trim();
    }
    
    // Extrair data folha
    const dataMatch = texto.match(/(?:Data|Data\s*Folha|Mês\/Ano)\s*[:]\s*(\d{2}\/\d{4})/i);
    if (dataMatch) {
        dados.data_folha = dataMatch[1];
    }
    
    // Extrair ano
    const anoMatch = texto.match(/Ano\s*[:]\s*(\d{4})/i);
    if (anoMatch) {
        dados.ano = anoMatch[1];
    } else {
        const anoDataMatch = texto.match(/Data\s*[:]\s*\d{2}\/(\d{4})/i);
        if (anoDataMatch) {
            dados.ano = anoDataMatch[1];
        }
    }
    
    // Extrair coordenadas
    const coordenadaPattern = /(\d{1,2})°(\d{1,2})'([\d.]+)"([NS])\s*(\d+\.?\d*)\s*(\d{1,2})°(\d{1,2})'([\d.]+)"([EW])\s*(\d+\.?\d*)/g;
    let match;
    while ((match = coordenadaPattern.exec(texto)) !== null) {
        dados.coordenadas.push({
            lat_gms: `${match[1]}°${match[2]}'${match[3]}"${match[4]}`,
            lat_utm: match[5],
            lon_gms: `${match[6]}°${match[7]}'${match[8]}"${match[9]}`,
            lon_utm: match[10]
        });
    }
    
    if (dados.coordenadas.length === 0) {
        dados.coordenadas = [{ lat_gms: '', lat_utm: '', lon_gms: '', lon_utm: '' }];
    }
    
    return dados;
}

// Função para ser chamada do Livewire - Memória Descritiva
window.extrairDadosMemoriaDescritiva = async function(fileInput, componentId) {
    const file = fileInput.files[0];
    if (!file) return;

    try {
        let dados;

        // Detectar tipo de arquivo
        if (file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')) {
            dados = await extrairDadosDePdf(file);
        } else {
            dados = await extrairDadosDeDocx(file);
        }

        // Enviar dados para o Livewire usando $call
        const component = Livewire.find(componentId);
        if (component) {
            component.call('processarDadosExtraidosMemoria', dados);
        }
    } catch (error) {
        console.error('Erro ao extrair dados:', error);
        const component = Livewire.find(componentId);
        if (component) {
            component.call('erroExtracaoMemoria', error.message);
        }
    }
};

// Função para ser chamada do Livewire - Ficheiro PDF do Croqui
window.extrairDadosFicheiroPdf = async function(fileInput, componentId) {
    const file = fileInput.files[0];
    if (!file) return;

    try {
        let dados;

        // Detectar tipo de arquivo
        if (file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')) {
            dados = await extrairDadosDePdf(file);
        } else {
            dados = await extrairDadosDeDocx(file);
        }

        // Enviar dados para o Livewire usando $call
        const component = Livewire.find(componentId);
        if (component) {
            component.call('processarDadosExtraidosPdf', dados);
        }
    } catch (error) {
        console.error('Erro ao extrair dados:', error);
        const component = Livewire.find(componentId);
        if (component) {
            component.call('erroExtracaoPdf', error.message);
        }
    }
};

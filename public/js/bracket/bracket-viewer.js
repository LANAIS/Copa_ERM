/**
 * Bracket Viewer Script
 * Script específico para la visualización de brackets en la aplicación
 */
 
document.addEventListener('DOMContentLoaded', function() {
    // Verificar que el elemento contenedor del bracket existe
    const bracketContainer = document.getElementById('brackets-viewer');
    if (!bracketContainer) {
        console.error('No se encontró el contenedor para el bracket');
        return;
    }

    // Verificar que los datos y la biblioteca están disponibles
    if (typeof window.bracketsViewer === 'undefined') {
        console.error('La biblioteca brackets-viewer no está cargada');
        return;
    }

    if (typeof window.bracketData === 'undefined') {
        console.error('No se encontraron los datos para el bracket');
        return;
    }

    // Datos del bracket
    const datos = window.bracketData;
    
    console.log('Inicializando bracket viewer con datos:', datos);
    
    try {
        // Configuración para el bracket viewer
        const stages = [{
            id: 1,
            name: datos.torneo.nombre,
            type: convertirTipoAFormatoViewer(datos.torneo.tipo),
            number: 1,
            settings: {}
        }];

        // Mapear los participantes a formato adecuado
        const participants = datos.participantes.map(p => ({
            id: p.id,
            name: p.nombre,
            tournament_id: datos.torneo.id
        }));

        // Mapear los enfrentamientos a formato adecuado
        const matches = datos.matches.map(match => ({
            id: match.id,
            number: match.numero_juego,
            stage_id: 1,
            group_id: match.grupo ? parseInt(match.grupo.charCodeAt(0) - 64) : null,
            round_id: match.ronda,
            opponent1: {
                id: match.equipo1_id,
                score: match.puntaje_equipo1,
                result: match.ganador_id === match.equipo1_id ? 'win' : 
                       (match.ganador_id && match.ganador_id !== match.equipo1_id ? 'loss' : null)
            },
            opponent2: {
                id: match.equipo2_id,
                score: match.puntaje_equipo2,
                result: match.ganador_id === match.equipo2_id ? 'win' : 
                       (match.ganador_id && match.ganador_id !== match.equipo2_id ? 'loss' : null)
            },
            status: match.estado
        }));

        // Variable global para almacenar la instancia del visualizador de brackets
        window.bracketInstance = window.bracketsViewer.render({
            stages: stages,
            matches: matches,
            participants: participants
        }, {
            selector: '#brackets-viewer',
            participantOriginPlacement: 'before',
            showSlotsOrigin: true,
            showFullParticipantNames: true,
            skipConsolationFinals: false,
            handleParticipantClick: function(participant) {
                console.log('Participante clickeado:', participant);
            },
            onMatchClick: function(match) {
                window.abrirModalResultado(match);
            }
        });

        // Añadir botones de exportación después de inicializar el bracket
        addExportButtons();

        console.log('Bracket inicializado correctamente');
    } catch (error) {
        console.error('Error al inicializar el bracket:', error);
    }
});

/**
 * Función para añadir botones de exportación al bracket
 */
function addExportButtons() {
    const container = document.getElementById('brackets-viewer');
    if (!container) return;
    
    // Crear contenedor para botones de exportación
    const exportButtonsContainer = document.createElement('div');
    exportButtonsContainer.className = 'flex justify-end space-x-2 mb-4';
    
    // Agregar botón para exportar a PNG
    const exportPngButton = document.createElement('button');
    exportPngButton.className = 'px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 flex items-center';
    exportPngButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" /></svg> Exportar PNG';
    exportPngButton.addEventListener('click', exportBracketToPng);
    
    // Agregar botón para exportar a PDF
    const exportPdfButton = document.createElement('button');
    exportPdfButton.className = 'px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 flex items-center';
    exportPdfButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" /></svg> Exportar PDF';
    exportPdfButton.addEventListener('click', exportBracketToPdf);
    
    // Agregar botón para imprimir directamente
    const printButton = document.createElement('button');
    printButton.className = 'px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center';
    printButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" /></svg> Imprimir';
    printButton.addEventListener('click', printBracket);
    
    // Añadir botones al contenedor
    exportButtonsContainer.appendChild(exportPngButton);
    exportButtonsContainer.appendChild(exportPdfButton);
    exportButtonsContainer.appendChild(printButton);
    
    // Insertar el contenedor antes del bracket
    container.parentNode.insertBefore(exportButtonsContainer, container);
}

/**
 * Función para imprimir el bracket directamente
 */
function printBracket() {
    // Crear un iframe oculto para la impresión
    const printFrame = document.createElement('iframe');
    printFrame.style.position = 'fixed';
    printFrame.style.right = '0';
    printFrame.style.bottom = '0';
    printFrame.style.width = '0';
    printFrame.style.height = '0';
    printFrame.style.border = '0';
    
    document.body.appendChild(printFrame);
    
    // Esperar a que el iframe se cargue
    printFrame.onload = function() {
        // Obtener el contenido del bracket
        const bracketEl = document.getElementById('brackets-viewer');
        const tournamentName = window.bracketData.torneo.nombre;
        
        // Crear contenido para el iframe
        const frameDoc = printFrame.contentDocument || printFrame.contentWindow.document;
        frameDoc.open();
        frameDoc.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>${tournamentName} - Bracket</title>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/brackets-viewer@latest/dist/brackets-viewer.min.css" />
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    h1 { text-align: center; margin-bottom: 20px; }
                    .bracket-container { overflow: auto; }
                    @media print {
                        @page { size: landscape; }
                        .bracket-container { page-break-inside: avoid; }
                    }
                </style>
            </head>
            <body>
                <h1>${tournamentName}</h1>
                <div class="bracket-container">
                    ${bracketEl.outerHTML}
                </div>
                <script>
                    window.onload = function() {
                        setTimeout(function() {
                            window.print();
                            setTimeout(function() {
                                window.close();
                            }, 500);
                        }, 300);
                    };
                </script>
            </body>
            </html>
        `);
        frameDoc.close();
    };
}

/**
 * Exportar bracket a PNG
 */
function exportBracketToPng() {
    try {
        // Verificar que html2canvas está disponible
        if (typeof html2canvas === 'undefined') {
            alert('La biblioteca de exportación no está disponible');
            return;
        }
        
        // Crear un nuevo elemento para realizar la captura
        const tournamentName = window.bracketData.torneo.nombre;
        const bracketEl = document.querySelector('.brackets-viewer');
        
        // Mostrar mensaje de carga
        const loadingMsg = document.createElement('div');
        loadingMsg.textContent = 'Generando imagen...';
        loadingMsg.style.position = 'fixed';
        loadingMsg.style.top = '50%';
        loadingMsg.style.left = '50%';
        loadingMsg.style.transform = 'translate(-50%, -50%)';
        loadingMsg.style.padding = '10px 20px';
        loadingMsg.style.background = 'rgba(0, 0, 0, 0.7)';
        loadingMsg.style.color = 'white';
        loadingMsg.style.borderRadius = '5px';
        loadingMsg.style.zIndex = '9999';
        document.body.appendChild(loadingMsg);
        
        // Crear una nueva ventana para mostrar el bracket
        const printWindow = window.open('', '_blank');
        
        // Estilo para la nueva ventana
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>${tournamentName} - Bracket</title>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/brackets-viewer@latest/dist/brackets-viewer.min.css" />
                <style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: white; }
                    .bracket-container { background-color: white; padding: 20px; max-width: 100%; overflow: auto; }
                    .controls { margin: 20px 0; text-align: center; }
                    button { background-color: #4F46E5; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
                    h1 { text-align: center; }
                </style>
            </head>
            <body>
                <h1>${tournamentName}</h1>
                <div id="capture-area" class="bracket-container">
                    ${bracketEl.outerHTML}
                </div>
                <div class="controls">
                    <button id="download-btn">Descargar Imagen</button>
                    <button id="close-btn">Cerrar</button>
                </div>
                
                <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
                <script>
                    document.getElementById('close-btn').addEventListener('click', function() {
                        window.close();
                    });
                    
                    document.getElementById('download-btn').addEventListener('click', function() {
                        const captureArea = document.getElementById('capture-area');
                        html2canvas(captureArea, {
                            scale: 2,
                            logging: true,
                            backgroundColor: '#ffffff'
                        }).then(function(canvas) {
                            const link = document.createElement('a');
                            link.download = 'bracket-${tournamentName.replace(/[^a-z0-9]/gi, '-').toLowerCase()}-${new Date().toISOString().slice(0, 10)}.png';
                            link.href = canvas.toDataURL('image/png');
                            link.click();
                        });
                    });
                </script>
            </body>
            </html>
        `);
        
        // Cerrar el mensaje de carga
        loadingMsg.remove();
        
    } catch (error) {
        console.error('Error en exportación PNG:', error);
        alert('Ocurrió un error al exportar. Por favor intente nuevamente.');
    }
}

/**
 * Exportar bracket a PDF
 */
function exportBracketToPdf() {
    try {
        // Crear un nuevo elemento para realizar la captura
        const tournamentName = window.bracketData.torneo.nombre;
        const bracketEl = document.querySelector('.brackets-viewer');
        
        // Mostrar mensaje de carga
        const loadingMsg = document.createElement('div');
        loadingMsg.textContent = 'Preparando para exportar a PDF...';
        loadingMsg.style.position = 'fixed';
        loadingMsg.style.top = '50%';
        loadingMsg.style.left = '50%';
        loadingMsg.style.transform = 'translate(-50%, -50%)';
        loadingMsg.style.padding = '10px 20px';
        loadingMsg.style.background = 'rgba(0, 0, 0, 0.7)';
        loadingMsg.style.color = 'white';
        loadingMsg.style.borderRadius = '5px';
        loadingMsg.style.zIndex = '9999';
        document.body.appendChild(loadingMsg);
        
        // Crear una nueva ventana para mostrar el bracket
        const printWindow = window.open('', '_blank');
        
        // Estilo para la nueva ventana
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>${tournamentName} - Bracket</title>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/brackets-viewer@latest/dist/brackets-viewer.min.css" />
                <style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: white; }
                    .bracket-container { background-color: white; padding: 20px; max-width: 100%; overflow: auto; }
                    .controls { margin: 20px 0; text-align: center; }
                    button { background-color: #4F46E5; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin: 0 5px; }
                    button.pdf-btn { background-color: #E53E3E; }
                    h1 { text-align: center; }
                </style>
            </head>
            <body>
                <h1>${tournamentName}</h1>
                <div id="capture-area" class="bracket-container">
                    ${bracketEl.outerHTML}
                </div>
                <div class="controls">
                    <button id="download-pdf-btn" class="pdf-btn">Exportar PDF</button>
                    <button id="close-btn">Cerrar</button>
                </div>
                
                <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
                <script>
                    document.getElementById('close-btn').addEventListener('click', function() {
                        window.close();
                    });
                    
                    document.getElementById('download-pdf-btn').addEventListener('click', function() {
                        const captureArea = document.getElementById('capture-area');
                        const loadingMsg = document.createElement('div');
                        loadingMsg.textContent = 'Generando PDF...';
                        loadingMsg.style.position = 'fixed';
                        loadingMsg.style.top = '50%';
                        loadingMsg.style.left = '50%';
                        loadingMsg.style.transform = 'translate(-50%, -50%)';
                        loadingMsg.style.padding = '10px 20px';
                        loadingMsg.style.background = 'rgba(0, 0, 0, 0.7)';
                        loadingMsg.style.color = 'white';
                        loadingMsg.style.borderRadius = '5px';
                        loadingMsg.style.zIndex = '9999';
                        document.body.appendChild(loadingMsg);
                        
                        html2canvas(captureArea, {
                            scale: 2,
                            logging: true,
                            backgroundColor: '#ffffff'
                        }).then(function(canvas) {
                            try {
                                const { jsPDF } = window.jspdf;
                                
                                // Determinar orientación
                                const orientation = captureArea.scrollWidth > captureArea.scrollHeight ? 'landscape' : 'portrait';
                                
                                const pdf = new jsPDF({
                                    orientation: orientation,
                                    unit: 'mm',
                                    format: 'a4'
                                });
                                
                                // Ajustar la imagen al PDF
                                const imgData = canvas.toDataURL('image/png');
                                const pdfWidth = pdf.internal.pageSize.getWidth();
                                const pdfHeight = pdf.internal.pageSize.getHeight();
                                const ratio = Math.min(pdfWidth / canvas.width, pdfHeight / canvas.height) * 0.9;
                                const imgWidth = canvas.width * ratio;
                                const imgHeight = canvas.height * ratio;
                                const x = (pdfWidth - imgWidth) / 2;
                                const y = (pdfHeight - imgHeight) / 2;
                                
                                // Añadir título
                                pdf.setFontSize(14);
                                pdf.text('${tournamentName}', pdfWidth / 2, 10, { align: 'center' });
                                
                                // Añadir imagen del bracket
                                pdf.addImage(imgData, 'PNG', x, 15, imgWidth, imgHeight);
                                
                                // Guardar PDF
                                pdf.save('bracket-${tournamentName.replace(/[^a-z0-9]/gi, '-').toLowerCase()}-${new Date().toISOString().slice(0, 10)}.pdf');
                                loadingMsg.remove();
                            } catch(err) {
                                console.error('Error generando PDF:', err);
                                loadingMsg.remove();
                                alert('Error al generar el PDF: ' + err.message);
                            }
                        }).catch(function(err) {
                            console.error('Error en html2canvas:', err);
                            loadingMsg.remove();
                            alert('Error al capturar el bracket: ' + err.message);
                        });
                    });
                </script>
            </body>
            </html>
        `);
        
        // Cerrar el mensaje de carga
        loadingMsg.remove();
        
    } catch (error) {
        console.error('Error en exportación PDF:', error);
        alert('Ocurrió un error al exportar. Por favor intente nuevamente.');
    }
}

/**
 * Función para convertir el tipo de torneo al formato esperado por brackets-viewer
 */
function convertirTipoAFormatoViewer(tipo) {
    switch(tipo) {
        case 'eliminacion_directa': return 'single_elimination';
        case 'eliminacion_doble': return 'double_elimination';
        case 'todos_contra_todos': return 'round_robin';
        case 'fase_grupos_eliminacion': return 'groups';
        case 'grupos': return 'groups';
        case 'suizo': return 'swiss';
        default: return 'single_elimination';
    }
}

/**
 * Función para abrir el modal de resultados
 * Debe ser accesible desde el ámbito global para que el bracket-viewer pueda llamarla
 */
window.abrirModalResultado = function(match) {
    // Solo permitir editar si ambos equipos están asignados
    if (!match.opponent1.id || !match.opponent2.id) {
        return;
    }
    
    // Buscar los nombres de los equipos
    const equipo1 = window.bracketData.participantes.find(p => p.id === match.opponent1.id);
    const equipo2 = window.bracketData.participantes.find(p => p.id === match.opponent2.id);
    
    document.getElementById('enfrentamiento_id').value = match.id;
    document.getElementById('equipo1-nombre').textContent = equipo1 ? equipo1.nombre : 'Equipo 1';
    document.getElementById('equipo2-nombre').textContent = equipo2 ? equipo2.nombre : 'Equipo 2';
    document.getElementById('puntaje_equipo1').value = match.opponent1.score || 0;
    document.getElementById('puntaje_equipo2').value = match.opponent2.score || 0;
    
    const modal = document.getElementById('modal-resultado');
    modal.classList.remove('hidden');
}; 
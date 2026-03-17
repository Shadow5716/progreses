document.addEventListener('DOMContentLoaded', function () {
    const $btnExportar = document.querySelector("#btnExportar"),
          $tabla = document.querySelector("#tabla");

    $btnExportar.addEventListener("click", async function () {
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('Reporte de Gestión');

        try {
            // 1. Cargar el Logo Institucional
            const response = await fetch('imagenes/alcaldia-maracaibo.png');
            const blob = await response.blob();
            const arrayBuffer = await blob.arrayBuffer();
            
            const logoId = workbook.addImage({
                buffer: arrayBuffer,
                extension: 'png',
            });

            // Posicionar logo (Esquina superior izquierda)
            worksheet.addImage(logoId, {
                tl: { col: 0.1, row: 0.2 },
                ext: { width: 150, height: 45 }
            });

        } catch (error) {
            console.error("Logo no encontrado, continuando con el texto.");
        }

        // 2. Título Centrado (Combinamos desde la columna B hasta la I para evitar el logo)
        worksheet.mergeCells('B2:I3');
        const titleCell = worksheet.getCell('B2');
        titleCell.value = "Programa de Reportes de Gestión - Reporte de Solicitudes dirigidas a la Dirección de Tecnología";
        
        titleCell.font = { name: 'Segoe UI', size: 14, bold: true, color: { argb: 'FF164377' } };
        titleCell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };

        // 3. Fecha y Hora de Emisión (Pequeña en la parte superior derecha)
        worksheet.getCell('I1').value = `Emitido el: ${new Date().toLocaleString()}`;
        worksheet.getCell('I1').font = { size: 9, italic: true };
        worksheet.getCell('I1').alignment = { horizontal: 'right' };

        // 4. Espaciado e inicio de tabla
        worksheet.addRow([]); worksheet.addRow([]); worksheet.addRow([]); worksheet.addRow([]);

        // 5. Encabezados
        const headers = [];
        $tabla.querySelectorAll("thead th").forEach((th, index) => {
            if (index !== 9) headers.push(th.innerText.trim());
        });
        const headerRow = worksheet.addRow(headers);

        headerRow.eachCell((cell) => {
            cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF164377' } };
            cell.font = { color: { argb: 'FFFFFFFF' }, bold: true };
            cell.alignment = { vertical: 'middle', horizontal: 'center' };
            cell.border = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };
        });

        // 6. Datos de la Tabla
        let contadorFilas = 0;
        $tabla.querySelectorAll("tbody tr").forEach(tr => {
            const rowData = [];
            const cells = tr.querySelectorAll("td");
            
            if (cells.length > 1) { 
                contadorFilas++;
                cells.forEach((td, index) => {
                    if (index !== 9) {
                        rowData.push(td.innerText.replace(/\s+/g, ' ').trim());
                    }
                });
                const dataRow = worksheet.addRow(rowData);
                dataRow.eachCell(cell => {
                    cell.alignment = { vertical: 'middle', wrapText: true };
                    cell.border = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };
                });
            }
        });

        // 7. Fila de Totales al final
        worksheet.addRow([]); // Espacio en blanco
        const totalRow = worksheet.addRow(['', '', '', '', '', '', 'TOTAL SOLICITUDES EN REPORTE:', contadorFilas]);
        totalRow.font = { bold: true };
        totalRow.getCell(7).alignment = { horizontal: 'right' };
        totalRow.getCell(8).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE8F5E9' } }; // Fondo verde suave

        // 8. Configuración de Columnas
        worksheet.columns = [
            { width: 8 }, { width: 15 }, { width: 30 }, { width: 25 }, 
            { width: 25 }, { width: 20 }, { width: 45 }, { width: 15 }, { width: 20 }
        ];

        // 9. Exportación
        const buffer = await workbook.xlsx.writeBuffer();
        const fileBlob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(fileBlob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Reporte_Tecnologia_${new Date().toISOString().slice(0,10)}.xlsx`;
        a.click();
        window.URL.revokeObjectURL(url);
    });
});

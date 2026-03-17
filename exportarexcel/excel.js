document.addEventListener('DOMContentLoaded', function () {
    const $btnExportar = document.querySelector("#btnExportar"),
        $tabla = document.querySelector("#tabla");

        let tableExport = new TableExport($tabla, {
            exportButtons: false,
            filename: "Nombre del archivo",
            sheetname: "Datos",
        });

    $btnExportar.addEventListener("click", function () {
        let datos = tableExport.getExportData();
        let preferenciasDocumento = datos.tabla.xlsx;
        tableExport.export2file(
            preferenciasDocumento.data, 
            preferenciasDocumento.mimeType, 
            preferenciasDocumento.filename, 
            preferenciasDocumento.fileExtension, 
            preferenciasDocumento.merges, 
            preferenciasDocumento.RTL, 
            preferenciasDocumento.sheetname);
    });
});
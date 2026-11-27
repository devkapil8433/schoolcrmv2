function shareFeeReportPdf(table, title) {
    if (typeof pdfMake === 'undefined') {
        window.alert('PDF support is not loaded. Please refresh the page and try again.');
        return;
    }

    var exportData = table.buttons.exportData({
        columns: function() { return true; },
        modifier: { page: 'all' }
    });
    var tableRows = [exportData.header].concat(exportData.body);
    var columnCount = exportData.header.length;
    var fontSize = columnCount > 13 ? 5 : (columnCount > 9 ? 6 : 7);
    var pdfDefinition = {
        pageSize: 'A4',
        pageOrientation: 'landscape',
        pageMargins: [8, 10, 8, 10],
        content: [
            { text: title, style: 'title' },
            {
                table: {
                    headerRows: 1,
                    dontBreakRows: true,
                    widths: Array(columnCount).fill('*'),
                    body: tableRows
                },
                layout: 'lightHorizontalLines'
            }
        ],
        defaultStyle: { fontSize: fontSize },
        styles: {
            title: { fontSize: 11, bold: true, margin: [0, 0, 0, 7] },
            tableHeader: { bold: true, fontSize: fontSize }
        }
    };
    var fileName = title.replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '').toLowerCase() + '.pdf';
    var whatsappUrl = 'https://wa.me/?text=' + encodeURIComponent(title + ' PDF is downloaded. Please attach it to the WhatsApp group.');
    var canShareFiles = false;
    var whatsappWindow = null;

    if (navigator.share && navigator.canShare && typeof File !== 'undefined') {
        try {
            canShareFiles = navigator.canShare({
                files: [new File([], fileName, { type: 'application/pdf' })]
            });
        } catch (error) {
            console.warn('File sharing capability check failed; using the download fallback.', error);
            canShareFiles = false;
        }
    }
    if (!canShareFiles) {
        whatsappWindow = window.open(whatsappUrl, '_blank');
    }

    pdfMake.createPdf(pdfDefinition).getBlob(function(blob) {
        var pdfFile = new File([blob], fileName, { type: 'application/pdf' });
        if (canShareFiles && navigator.canShare({ files: [pdfFile] })) {
            navigator.share({ files: [pdfFile], title: title, text: title })
                .catch(function(error) {
                    if (error.name !== 'AbortError') {
                        console.error('Unable to share the fee report PDF:', error);
                        window.alert('PDF share was unavailable. The PDF will be downloaded so you can attach it in a WhatsApp group.');
                        pdfMake.createPdf(pdfDefinition).download(fileName);
                    }
                });
            return;
        }

        pdfMake.createPdf(pdfDefinition).download(fileName);
        if (whatsappWindow && whatsappWindow.closed) {
            whatsappWindow.location.href = whatsappUrl;
        }
        window.alert('PDF downloaded. In WhatsApp, open the required group and attach the downloaded PDF.');
    });
}

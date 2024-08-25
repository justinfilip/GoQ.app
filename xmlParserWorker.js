self.onmessage = function(event) {
    const { text, fileType, offset } = event.data;
    const isFirstChunk = offset === 0;

    let data = [];
    if (fileType.includes('xml')) {
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(text, "text/xml");
        const root = xmlDoc.documentElement;
        const rows = Array.from(root.children);
        rows.forEach(rowNode => {
            const row = {};
            const cells = rowNode.children;
            Array.from(cells).forEach(cell => {
                const header = cell.tagName;
                row[header] = cell.textContent;
            });
            data.push(row);
        });
    }

    self.postMessage({ data, isFirstChunk });
};

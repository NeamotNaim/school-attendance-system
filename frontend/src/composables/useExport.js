import { useToast } from './useToast';

const toast = useToast();

export function useExport() {
  // Export to CSV
  const exportToCSV = (data, filename) => {
    if (!data || data.length === 0) {
      toast.warning('No data to export');
      return;
    }

    const headers = Object.keys(data[0]);
    const csvContent = [
      headers.join(','),
      ...data.map((row) =>
        headers.map((header) => {
          const value = row[header];
          // Escape commas and quotes
          if (typeof value === 'string' && (value.includes(',') || value.includes('"'))) {
            return `"${value.replace(/"/g, '""')}"`;
          }
          return value ?? '';
        }).join(',')
      ),
    ].join('\n');

    downloadFile(csvContent, `${filename}.csv`, 'text/csv;charset=utf-8;');
    toast.success('CSV file downloaded successfully!');
  };

  // Export to Excel (using HTML table method)
  const exportToExcel = (data, filename, title = '') => {
    if (!data || data.length === 0) {
      toast.warning('No data to export');
      return;
    }

    const headers = Object.keys(data[0]);
    
    let html = `
      <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
      <head>
        <meta charset="utf-8">
        <style>
          table { border-collapse: collapse; width: 100%; }
          th { background-color: #4f46e5; color: white; padding: 12px; text-align: left; font-weight: bold; border: 1px solid #ddd; }
          td { padding: 10px; border: 1px solid #ddd; }
          tr:nth-child(even) { background-color: #f9fafb; }
          .title { font-size: 18px; font-weight: bold; margin-bottom: 10px; color: #1e293b; }
        </style>
      </head>
      <body>
    `;

    if (title) {
      html += `<div class="title">${title}</div>`;
    }

    html += '<table><thead><tr>';
    headers.forEach((header) => {
      html += `<th>${formatHeader(header)}</th>`;
    });
    html += '</tr></thead><tbody>';

    data.forEach((row) => {
      html += '<tr>';
      headers.forEach((header) => {
        html += `<td>${row[header] ?? ''}</td>`;
      });
      html += '</tr>';
    });

    html += '</tbody></table></body></html>';

    downloadFile(html, `${filename}.xls`, 'application/vnd.ms-excel');
    toast.success('Excel file downloaded successfully!');
  };

  // Export to PDF (using HTML to print method)
  const exportToPDF = (data, filename, title = '') => {
    if (!data || data.length === 0) {
      toast.warning('No data to export');
      return;
    }

    const headers = Object.keys(data[0]);
    
    let html = `
      <!DOCTYPE html>
      <html>
      <head>
        <meta charset="utf-8">
        <title>${filename}</title>
        <style>
          @page { size: A4 landscape; margin: 20mm; }
          body { font-family: Arial, sans-serif; font-size: 12px; }
          .header { text-align: center; margin-bottom: 20px; }
          .title { font-size: 20px; font-weight: bold; color: #1e293b; margin-bottom: 5px; }
          .subtitle { font-size: 12px; color: #64748b; }
          table { width: 100%; border-collapse: collapse; margin-top: 10px; }
          th { background-color: #4f46e5; color: white; padding: 10px; text-align: left; font-weight: bold; border: 1px solid #ddd; }
          td { padding: 8px; border: 1px solid #ddd; }
          tr:nth-child(even) { background-color: #f9fafb; }
          .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #94a3b8; }
        </style>
      </head>
      <body>
        <div class="header">
          <div class="title">${title || filename}</div>
          <div class="subtitle">Generated on ${new Date().toLocaleString()}</div>
        </div>
        <table>
          <thead>
            <tr>
    `;

    headers.forEach((header) => {
      html += `<th>${formatHeader(header)}</th>`;
    });

    html += '</tr></thead><tbody>';

    data.forEach((row) => {
      html += '<tr>';
      headers.forEach((header) => {
        html += `<td>${row[header] ?? ''}</td>`;
      });
      html += '</tr>';
    });

    html += `
          </tbody>
        </table>
        <div class="footer">School Attendance Management System</div>
      </body>
      </html>
    `;

    // Open in new window and trigger print
    const printWindow = window.open('', '_blank');
    printWindow.document.write(html);
    printWindow.document.close();
    
    // Wait for content to load then print
    printWindow.onload = () => {
      printWindow.focus();
      printWindow.print();
      toast.success('PDF print dialog opened!');
    };
  };

  // Helper function to download file
  const downloadFile = (content, filename, mimeType) => {
    const blob = new Blob([content], { type: mimeType });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
  };

  // Helper function to format header names
  const formatHeader = (header) => {
    return header
      .replace(/_/g, ' ')
      .replace(/\b\w/g, (char) => char.toUpperCase());
  };

  return {
    exportToCSV,
    exportToExcel,
    exportToPDF,
  };
}

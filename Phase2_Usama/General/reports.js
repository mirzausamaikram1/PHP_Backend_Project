// reports.js - Add some fun interactivity to the reports page!

// Wait for the page to fully load
document.addEventListener('DOMContentLoaded', function() {
  // Get the table and form elements
  const table = document.querySelector('.table');
  const startDateInput = document.querySelector('input[name="start_date"]');
  const endDateInput = document.querySelector('input[name="end_date"]');
  const filterButton = document.querySelector('button[type="submit"]');

  // Add a button to export the table as a CSV file
  const exportBtn = document.createElement('button');
  exportBtn.textContent = 'Export to CSV';
  exportBtn.className = 'btn btn-success mb-3';
  exportBtn.style.marginLeft = '10px';
  document.querySelector('.col-md-4.align-self-end').appendChild(exportBtn);

  // Function to convert table to CSV
  function downloadCSV() {
    let csv = [];
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
      const rowData = [];
      const cols = row.querySelectorAll('td, th');
      cols.forEach(col => {
        rowData.push(col.textContent);
      });
      csv.push(rowData.join(','));
    });

    // Create a downloadable file
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.href = URL.createObjectURL(csvFile);
    downloadLink.download = 'reports_' + new Date().toISOString().slice(0, 10) + '.csv';
    downloadLink.click();
  }

  // Add click event to export button
  exportBtn.addEventListener('click', downloadCSV);

  // Add date validation
  filterButton.addEventListener('click', function(e) {
    const startDate = new Date(startDateInput.value);
    const endDate = new Date(endDateInput.value);

    // Check if end date is before start date
    if (startDate > endDate && endDateInput.value) {
      e.preventDefault(); // Stop the form from submitting
      alert('Oops! End date cannot be before start date. Please try again!');
      endDateInput.value = ''; // Clear the invalid end date
    }
  });
});
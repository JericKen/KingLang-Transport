// ==========================================
// BigQuery Configuration Updater
// ==========================================

const fs = require('fs');
const path = require('path');

// ⚠️ UPDATE THESE VALUES WITH YOUR BIGQUERY DETAILS
const config = {
  projectId: 'bigquery-analytics-470015', // ✅ Your actual Project ID
  datasetId: 'bus_booking_analytics', // ⚠️ You'll need to create this dataset in BigQuery

  // Optional: Update table names if different from defaults
  tableNames: {
    bookings: 'bookings', // Change if your table name is different
    buses: 'buses',
    feedback: 'feedback',
    drivers: 'drivers',
    marketing_costs: 'marketing_costs',
  },
};

// ==========================================
// Script Logic (Don't modify below)
// ==========================================

const analyticsDir = path.join(__dirname, 'analytics');

// Read all SQL files
const sqlFiles = fs
  .readdirSync(analyticsDir)
  .filter((file) => file.endsWith('.sql'))
  .map((file) => path.join(analyticsDir, file));

let totalReplacements = 0;

sqlFiles.forEach((filePath) => {
  let content = fs.readFileSync(filePath, 'utf8');
  const originalContent = content;

  // Replace project.dataset with actual values
  const fullDatasetPath = `${config.projectId}.${config.datasetId}`;
  content = content.replace(/project\.dataset/g, fullDatasetPath);

  // Replace table names if customized
  Object.entries(config.tableNames).forEach(([defaultName, customName]) => {
    if (defaultName !== customName) {
      const regex = new RegExp(`\\.${defaultName}\\b`, 'g');
      content = content.replace(regex, `.${customName}`);
    }
  });

  // Count replacements
  if (content !== originalContent) {
    const replacements = (originalContent.match(/project\.dataset/g) || [])
      .length;
    totalReplacements += replacements;

    // Write updated content back
    fs.writeFileSync(filePath, content, 'utf8');
    console.log(
      `✅ Updated ${path.basename(filePath)} (${replacements} replacements)`
    );
  }
});

// Update PHP backend file
const phpFile = path.join(__dirname, 'backend_integration_example.php');
if (fs.existsSync(phpFile)) {
  let phpContent = fs.readFileSync(phpFile, 'utf8');

  phpContent = phpContent.replace(
    /\$this->projectId = 'your-project-id';/,
    `$this->projectId = '${config.projectId}';`
  );

  phpContent = phpContent.replace(
    /\$this->datasetId = 'your-dataset';/,
    `$this->datasetId = '${config.datasetId}';`
  );

  fs.writeFileSync(phpFile, phpContent, 'utf8');
  console.log(`✅ Updated ${path.basename(phpFile)}`);
}

console.log(`\n🎉 Configuration update complete!`);
console.log(`📊 Total replacements: ${totalReplacements}`);
console.log(`📁 Files updated: ${sqlFiles.length + 1}`);
console.log(`\n✨ Your BigQuery credentials are now:`);
console.log(`   Project ID: ${config.projectId}`);
console.log(`   Dataset ID: ${config.datasetId}`);
console.log(`\n⚠️  Next steps:`);
console.log(`   1. Review the updated SQL files`);
console.log(`   2. Test a simple query in BigQuery console`);
console.log(`   3. Update service account credentials in PHP`);

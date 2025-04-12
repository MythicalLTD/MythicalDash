#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { promisify } = require('util');
const readFileAsync = promisify(fs.readFile);

// File extensions to include
const VALID_EXTENSIONS = ['.vue', '.ts', '.js', '.php', '.css', '.yml', '.yaml', '.json', '.sql'];
// Directories to exclude
const EXCLUDED_DIRS = ['node_modules', 'vendor', 'cache', '.cache', 'packages','dist','assets'];

// Process arguments
const targetDir = process.argv[2] || '.';

// Regular expressions for detecting comments
const COMMENT_PATTERNS = {
  '.js': [/\/\/.*$/m, /\/\*[\s\S]*?\*\//g],
  '.ts': [/\/\/.*$/m, /\/\*[\s\S]*?\*\//g],
  '.vue': [/<!--[\s\S]*?-->/g, /\/\/.*$/m, /\/\*[\s\S]*?\*\//g],
  '.php': [/\/\/.*$/m, /\/\*[\s\S]*?\*\//g, /#.*$/m],
  '.css': [/\/\*[\s\S]*?\*\//g]
};

async function countLinesInFile(filePath) {
  try {
    let content = await readFileAsync(filePath, 'utf8');
    const ext = path.extname(filePath).toLowerCase();
    
    // Remove comments based on file extension
    if (COMMENT_PATTERNS[ext]) {
      COMMENT_PATTERNS[ext].forEach(pattern => {
        content = content.replace(pattern, '');
      });
    }
    
    const lines = content.split('\n');
    const nonEmptyLines = lines.filter(line => line.trim().length > 0);
    return nonEmptyLines.length;
  } catch (error) {
    console.error(`Error reading file ${filePath}: ${error.message}`);
    return 0;
  }
}

async function traverseDirectory(dir, results = {}) {
  try {
    const entries = fs.readdirSync(dir, { withFileTypes: true });
    
    for (const entry of entries) {
      const fullPath = path.join(dir, entry.name);
      
      if (entry.isDirectory()) {
        // Skip excluded directories
        if (EXCLUDED_DIRS.some(excluded => entry.name.toLowerCase().includes(excluded.toLowerCase()))) {
          continue;
        }
        await traverseDirectory(fullPath, results);
      } else if (entry.isFile()) {
        const ext = path.extname(entry.name).toLowerCase();
        if (VALID_EXTENSIONS.includes(ext)) {
          const lineCount = await countLinesInFile(fullPath);
          if (!results[ext]) {
            results[ext] = { files: 0, lines: 0 };
          }
          results[ext].files++;
          results[ext].lines += lineCount;
        }
      }
    }
  } catch (error) {
    console.error(`Error traversing directory ${dir}: ${error.message}`);
  }
  
  return results;
}

async function main() {
  console.log(`Counting non-empty lines of code in ${targetDir}...`);
  console.log(`Including extensions: ${VALID_EXTENSIONS.join(', ')}`);
  console.log(`Excluding directories: ${EXCLUDED_DIRS.join(', ')}`);
  console.log('Excluding: comments and empty lines');
  
  const results = await traverseDirectory(targetDir);
  
  console.log('\nResults:');
  console.log('-'.repeat(50));
  
  let totalFiles = 0;
  let totalLines = 0;
  
  Object.entries(results).forEach(([ext, { files, lines }]) => {
    console.log(`${ext.padEnd(6)} | ${files.toString().padStart(6)} files | ${lines.toString().padStart(8)} lines`);
    totalFiles += files;
    totalLines += lines;
  });
  
  console.log('-'.repeat(50));
  console.log(`Total  | ${totalFiles.toString().padStart(6)} files | ${totalLines.toString().padStart(8)} lines`);
}

main().catch(error => {
  console.error('An error occurred:', error);
  process.exit(1);
});

// Builds the version 1 storefront scripts from the TypeScript sources in this folder:
//   includes/js/printessEditor.js       from printessEditor.ts
//   includes/js/printessWoocommerce.js  from printessSharedTools.ts followed by printessWoocommerce.ts
//
// Needs node and tsc (TypeScript) on the PATH. Run from anywhere: node js-src/build.mjs
//
// The sources came from shopIntegrations/baseintegration in the Printess repository (commit
// 7ab1eff66, 2026-07-09), whose build.sh also built the Shopify, Magento and Shopware scripts. This
// builds only what the WooCommerce plugin ships, and does KillExport's job itself: the files are
// loaded as plain scripts, so "import" lines become ";" and "export " goes, together with the line
// break before it. The result is written the way the plugin has always shipped it: UTF-8 with a
// BOM, no leading ";" lines, no source map comment, and no line break at the end.
//
// A Node script rather than a shell script: Plugin Check reports *.sh files in a plugin as errors.

import { execFileSync } from 'node:child_process';
import { readFileSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const here = dirname( fileURLToPath( import.meta.url ) );
const dist = join( here, 'dist' );
const out  = join( here, '..', 'includes', 'js' );

rmSync( dist, { recursive: true, force: true } );
execFileSync( 'tsc', [ '-p', join( here, 'tsconfig.json' ) ], { stdio: 'inherit', shell: 'win32' === process.platform } );

const read = ( name ) => {
	let s = readFileSync( join( dist, name + '.js' ), 'utf8' );
	s = s.replace( /^import [^\n]*;$/gm, ';' ).replace( /\r?\nexport /g, '' ).replace( /^export /, '' );
	s = s.replace( /^(?:;\s*)+/, '' );                          // the ";" left where imports and types were
	s = s.replace( /\r?\n\/\/# sourceMappingURL=[^\n]*/, '' );  // no source maps are shipped
	return s.replace( /\s+$/, '' );
};
const write = ( name, s ) => {
	writeFileSync( join( out, name ), '\uFEFF' + s );
	console.log( `${ name }: ${ Buffer.byteLength( s ) + 3 } bytes` );
};

write( 'printessEditor.js', read( 'printessEditor' ) );
write( 'printessWoocommerce.js', read( 'printessSharedTools' ) + '\n\n' + read( 'printessWoocommerce' ) );

rmSync( dist, { recursive: true, force: true } );

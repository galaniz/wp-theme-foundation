!function(){var e=window.blockUtils,o=e.getNamespace,t=e.getNamespaceObj;window.wp.domReady((function(){if(o(!0)){var e=t(o());if(e){var c=!1,n=!1;if(Object.getOwnPropertyDescriptor(e,"insert_block")&&(c=e.insert_block),Object.getOwnPropertyDescriptor(e,"insert_blocks")&&(n=e.insert_blocks),(c||n)&&(c&&(n=[{name:c,defaults:e.insert_block_defaults}]),n&&n.length)){var r=window.wp.data.select("core/block-editor").getBlocks();n.forEach((function(e){var o=e.name,t=!1;if(r.forEach((function(e){e.name===o&&(t=!0)})),!t){var c=window.wp.blocks.createBlock(o,e.defaults),n=window.wp.data.dispatch("core/block-editor").insertBlock(c,0);console.log("BLOCK ".concat(o," ADDED"),n)}}))}}}}))}();
!function(){var e=blockUtils,t=e.getNamespace,l=e.getNamespaceObj,n=wp.components,o=n.Panel,a=n.PanelBody,r=n.SelectControl,c=wp.blockEditor,p=c.InspectorControls,m=c.InnerBlocks,i=wp.element.Fragment,s=wp.blocks.registerBlockType,u=t(!0),g=u+"contact-form-group-bottom",w=l(t()),b=w.blocks[g].attr,d=w.blocks[g].default;s(g,{title:"Field Group Bottom",category:"theme-blocks",icon:"email",attributes:b,parent:[u+"contact-form-group"],edit:function(e){var t=e.attributes,l=e.setAttributes,n=t.gap,c=void 0===n?d.gap:n;return[w.gap_options.length?wp.element.createElement(i,null,wp.element.createElement(p,null,wp.element.createElement(a,{title:"Field Group Bottom Options"},wp.element.createElement(r,{label:"Fields Gap",value:c,options:w.gap_options,onChange:function(e){return l({gap:e})}})))):"",wp.element.createElement(o,null,wp.element.createElement(a,null,wp.element.createElement("div",{className:"l-section"},wp.element.createElement(m,{allowedBlocks:[u+"contact-form-field"]}))))]},save:function(){return wp.element.createElement(m.Content,null)}})}();
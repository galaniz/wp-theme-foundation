!function(){var e=window.blockUtils,t=e.getNamespace,n=e.getNamespaceObj,l=window.wp.components,a=l.Panel,o=l.PanelBody,c=l.TextControl,r=l.SelectControl,i=window.wp.data.withSelect,m=window.wp.blockEditor,s=m.InspectorControls,u=m.InnerBlocks,p=m.PlainText,w=window.wp.element.Fragment,d=window.wp.blocks.registerBlockType,b=t(!0),g=b+"contact-form",v=n(t()),E=v.blocks[g].attr,f=v.blocks[g].default;d(g,{title:"Contact Form",category:"theme-blocks",icon:"email",attributes:E,edit:i((function(e,t){var n=t.clientId,l={clientId:n};return Object.getOwnPropertyDescriptor(t,"id")||(t.attributes.id=n),l}))((function(e){var t=e.attributes,n=e.setAttributes,l=t.email,i=void 0===l?f.email:l,m=t.subject,d=void 0===m?f.subject:m,g=t.submitText,E=void 0===g?f.submit_text:g,k=t.successMessage,C=void 0===k?f.success_message:k,h=t.gap,j=void 0===h?f.gap:h,x="";return v.gap_options.length&&(x=wp.element.createElement("div",null,wp.element.createElement(r,{label:"Fields Gap",value:j,options:v.gap_options,onChange:function(e){return n({gap:e})}}))),[wp.element.createElement(w,{key:"frag"},wp.element.createElement(s,null,wp.element.createElement(o,{title:"Form Options"},wp.element.createElement(c,{label:"To Email",value:i,onChange:function(e){return n({email:e})}}),wp.element.createElement(c,{label:"Subject",value:d,onChange:function(e){return n({subject:e})}}),wp.element.createElement(c,{label:"Submit Text",value:E,onChange:function(e){return n({submit_text:e})}}),x))),wp.element.createElement(a,{className:"o-form",key:"panel"},wp.element.createElement(o,{title:"Fields"},wp.element.createElement("div",{className:"l-section"},wp.element.createElement(u,{allowedBlocks:[b+"contact-form-field",b+"contact-form-group"]})),wp.element.createElement("div",{className:"l-section"},wp.element.createElement("div",null,wp.element.createElement(p,{value:C,onChange:function(e){return n({success_message:e})},placeholder:"Success message..."})))))]})),save:function(){return wp.element.createElement(u.Content,null)}})}();
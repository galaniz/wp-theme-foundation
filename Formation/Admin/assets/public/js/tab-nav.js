document.addEventListener("DOMContentLoaded",(function(){var e=[].slice.call(document.querySelectorAll(".js-section"));e.length>0&&e.forEach((function(e,t){var n=e.lastElementChild;if("H2"==n.tagName.toUpperCase()){var l=e.nextElementSibling;l.insertBefore(n,l.firstElementChild)}}))}));
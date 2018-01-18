/**
 * @Author: Andrea F. Daniele <afdaniele>
 * @Date:   Monday, January 15th 2018
 * @Email:  afdaniele@ttic.edu
 * @Last modified by:   afdaniele
 * @Last modified time: Tuesday, January 16th 2018
 */

 function allowDrop(ev) {
     ev.preventDefault();
 }

 function drag(ev) {
     ev.dataTransfer.setData("text", ev.target.id);
     console.log( ev );
 }

 function drop(ev) {
     ev.preventDefault();
     var data = ev.dataTransfer.getData("text");
     ev.target.appendChild(document.getElementById(data));
     $("#"+ev.target.id).css('border-color', 'gray');
 }


 function dragEnter(ev){
     ev.preventDefault();
     $("#"+ev.target.id).css('border-color', 'yellow');
 }

 function dragLeave(ev){
     ev.preventDefault();
     $("#"+ev.target.id).css('border-color', 'gray');
 }


 // $('#div5').on('dragover', function(e) {
 //     e.preventDefault();
 //     console.log('YAAAA');
 // });

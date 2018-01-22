/**
 * @Author: Andrea F. Daniele <afdaniele>
 * @Date:   Monday, January 15th 2018
 * @Email:  afdaniele@ttic.edu
 * @Last modified by:   afdaniele
 * @Last modified time: Wednesday, January 17th 2018
 */

 function allowDrop(ev) {
     ev.preventDefault();
 }

 function drag(ev) {
     ev.dataTransfer.setData("text", ev.target.id);
 }

 function drop(ev) {
     ev.preventDefault();
     var data = ev.dataTransfer.getData("text");
     ev.target.appendChild( document.getElementById(data).cloneNode(true) );
     $("#"+ev.target.id).css('border-color', 'black');
 }

 function dragEnter(ev){
     ev.preventDefault();
     $("#"+ev.target.id).css('background-color', '#fec612');
 }

 function dragLeave(ev){
     ev.preventDefault();
     $("#"+ev.target.id).css('background-color', 'black');
 }


 // $('#div5').on('dragover', function(e) {
 //     e.preventDefault();
 //     console.log('YAAAA');
 // });

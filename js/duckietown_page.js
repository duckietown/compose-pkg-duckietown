/**
 * @Author: Andrea F. Daniele <afdaniele>
 * @Date:   Monday, January 15th 2018
 * @Email:  afdaniele@ttic.edu
 * @Last modified by:   afdaniele
 * @Last modified time: Wednesday, February 7th 2018
 */



 $(document).ready(function(){
     // get toolbox, grid and trash
     var toolbox = $('#tiles_toolbox');
     var trash = $('#tiles_trash');
     var grid = $('#town_canvas');

     // let the toolbox tiles be draggable
    $( ".tile", toolbox ).draggable({
        revert: "invalid", // when not dropped, the item will revert back to its initial position
        containment: "document",
        helper: "clone",
        cursor: "move"
    });

    // let the grid be droppable, accepting tiles only from the toolbox (TODO: maybe we can also enable grid->grid)
    $( ".tile_grid_container", grid ).droppable({
        accept: "#tiles_toolbox > .tile",
        hoverClass: "grid_cell_hover",
        drop: function( event, ui ) {
            var tile = ui.draggable;
            var cell = $('#'+event.target.id);
            // update the image in the cell to simulate the copy of the tile
            fromToolboxToGrid( tile, cell );
        }
    });

    // let the tiles from the grid be draggable
    $( ".tile", grid ).draggable({
        revert: "invalid", // when not dropped, the item will revert back to its initial position
        containment: "document",
        helper: "clone",
        cursor: "move"
    });

    // let the trash be droppable, accepting tiles only from the grid
    trash.droppable({
        accept: ".tile_grid_container .tile",
        hoverClass: "grid_cell_hover",
        classes: {
            "ui-droppable-active" : "ui-state-highlight"
        },
        drop: function( event, ui ) {
            var tile = ui.draggable;
            // update the image in the cell to simulate the copy of the tile
            fromGridToTrash( tile );
        }
    });




    function fromToolboxToGrid( tile, destination_cell ){
        cell_img = destination_cell.find("img");
        cell_img.removeClass();                         // remove all the classes to clear orientation
        cell_img.attr('src', tile.attr('src'));         // copy image source
        cell_img.attr('class', tile.attr('class'));     // copy all the classes from the dropped tile
        cell_img.data('tile', tile.attr('id'));         // store the ID of the tile
    }


    function fromGridToTrash( tile ){
        empty_tile = $('#empty_0', toolbox);
        tile.removeClass();                             // remove all the classes to clear orientation
        tile.attr('src', empty_tile.attr('src'));       // restore the image source to that of an empty tile
        tile.attr('class', 'tile tile_0');              // restore the classes to those of an empty tile
        tile.data('tile', 'empty');                     // store the ID of the tile
    }






    function deleteTile( tile ) {
        item.fadeOut(function() {
            // do something
        });
    }


 });


//
// function allowDrop(ev) {
//     ev.preventDefault();
// }
//
// function drag(ev) {
//     ev.dataTransfer.setData("dragged-obj-id", ev.target.id);
// }
//
// function dragOverGridOnDrop(ev) {
//     ev.preventDefault();
//     // create a copy of the tile
//     var dragged_obj_id = ev.dataTransfer.getData("dragged-obj-id");
//     var new_node = $('#'+dragged_obj_id).clone() ; //document.getElementById(dragged_obj_id).cloneNode(true);
//     // set the attribute 'is-ongrid' to true for the new copy of the tile dropped on the grid
//
//     // console.log( new_node );
//     // new_node.data('is-ongrid', true);
//
//     new_node.appendTo('#slot_1_1');
//
//     // $( ev.target.id )
//
//     // new_node.setAttribute('is-ongrid', true);
//     // add the new tile as a child of the targeted cell
//     // ev.target.appendChild( new_node.get(0) );
//
//
//     console.log( ev );
//
//
//     $("#"+ev.target.id).css('border-color', 'black');
//  }
//


function dragOverGridOnEnter(ev){
    ev.preventDefault();
    $("#"+ev.target.id).css('background-color', '#fec612');
}

function dragOverGridOnLeave(ev){
    ev.preventDefault();
    $("#"+ev.target.id).css('background-color', 'black');
}


//
//
//
//
//
// function dragOverTrashOnDrop(ev){
//
//     ev.preventDefault();
//     var data = ev.dataTransfer.getData("dragged_obj_id");
//     var elem = document.getElementById(data);
//     elem.outerHTML = '';
//
//     console.log( $('#'+data).parent() );
//
//     delete elem;
//
//     $("#"+ev.target.id).css('background-color', 'inherit');
// }
//
// function dragOverTrashOnEnter(ev){
//     ev.preventDefault();
//     $("#"+ev.target.id).css('background-color', 'darkorange');
// }
//
// function dragOverTrashOnLeave(ev){
//     ev.preventDefault();
//     $("#"+ev.target.id).css('background-color', 'inherit');
// }





// Define constants:
var _duckietown_merge_svgs_horiz_offset = 436.907;
var _duckietown_merge_svgs_vert_offset = 436.907;


function merge_svgs( grid, toolbox_id ){


}

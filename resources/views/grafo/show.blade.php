@extends('layouts.app')
@section('content')
<div class="row center"><div class="col-lg-12 text-center">
<h3>({{ $aglomerado->codigo}}) {{ $aglomerado->nombre}}</h3>
Radio: {{ $radio->codigo}}
</div></div>
  <div class="row">
    </div>
</div>
@endsection
@section('content')
@endsection
@section('header_scripts')
<script src="https://unpkg.com/numeric/numeric-1.2.6.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cytoscape/3.14.0/cytoscape.min.js"></script>
<script src="https://unpkg.com/layout-base/layout-base.js"></script>
<script src="https://unpkg.com/cose-base/cose-base.js"></script>
<script src="/js/cytoscape-fcose.js"></script>
<script src="/js/cytoscape-cola.js"></script>
<script src="/js/cola.min.js"></script>
<style>
#grafo_cy {
  width: 400px;
  height: 500px;
  display: block;
}
.no-gutters {
  margin-right: 0;
  margin-left: 0;

  > .col,
  > [class*="col-"] {
    padding-right: 0;
    padding-left: 0;
  }
}
</style>
@endsection
@section('content_main')
<div class="container-xl" >
    <div class="col no-gutters">
      <div class="row no-gutters">
        <div class="col-sm-12">
        <pre style="line-height: initial;font-size: 75%;">
        {{ $radio->resultado ?? 'No hay resultado de segmenta' }}
        </pre>
        </div>
      </div>
      <div class="row no-gutters">
        <div class="col" title=MiniMap> {!! $radio->getSVG() !!}</div>
        <div class="col ">
            <div id=grafo_cy width= 400px; height= 500px
               title="Grafo de adyacencias" >
            </div>
            <div class="text-center">
        	  <button type="button" class="btn btn-primary" onClick="ordenar();"value="Ordenar">ReOrdenar</button>
            </div>
        </div>  
      </div>
      <div class="row no-gutters">
        <div class="col">
        <div class="row text-center border">
            <div class="col-sm-1 border">id</div>
            <div class="col-sm-1 border">Seg</div>
            <div class="col-sm-9 border">Descripción</div>
            <div class="col-sm-1 border">Viviendas</div>
        </div>
        @forelse ($segmentacion_data_listado as $segmento)
        <div class="row border">
        <div class="col-sm-1 ">{{ $segmento->segmento_id }}</div>
        <div class="col-sm-1 ">{{ $segmento->seg }}</div>
        <div class="col-sm-9 ">{{ $segmento->detalle }}</div>
        <div class="col-sm-1 ">{{ $segmento->vivs }}</div>
        </div>
        @empty
            <p>No hay segmentos</p>
        @endforelse
        </div>
      </div>
    </div>
</div>
@endsection
@section('footer_scripts')
	<script>
    let arrayOfClusterArrays = @json($segmentacion) ;  
    let clusterColors = ['#FF0', '#0FF', '#F0F', '#4139dd', '#d57dba', '#8dcaa4'
                        ,'#555','#CCC','#A00','#0A0','#00A','#F00','#0F0','#00F','#008','#800','#080'];
		var cy = cytoscape({

    container: document.getElementById('grafo_cy'), // container to render in

  elements: [ // list of graph elements to start with
    @foreach ($nodos as $nodo)
        { data: { group: 'nodes',mza: '{{ $nodo->mza_i }}',label: '{{ $nodo->label }}', conteo: '{{ $nodo->conteo }}', id: '{{ $nodo->mza_i }}-{{ $nodo->lado_i }}'  } },
    @endforeach    
    @foreach ($relaciones as $nodo)
        { data: { group: 'edges',tipo: '{{ $nodo->tipo }}', id: '{{ $nodo->mza_i }}-{{ $nodo->lado_i }}->{{ $nodo->mza_j }}-{{ $nodo->lado_j }}', source:'{{ $nodo->mza_i }}-{{ $nodo->lado_i }}', target:'{{ $nodo->mza_j }}-{{ $nodo->lado_j }}'} },
    @endforeach    
  ],
  style: [ // the stylesheet for the graph
    {
      selector: 'node',
      style: {
        'background-color': function (ele) {
					for (let i = 0; i < arrayOfClusterArrays.length; i++)
						if (arrayOfClusterArrays[i].includes(ele.data('id')))
                           if (i>clusterColors.length) {n=i-clusterColors.length;
                                                        if (n<0) n=-n;}
                            else n=i;
						if (clusterColors[n]!=null) return clusterColors[n];
					return '#000000';
				},
        'label': 'data(conteo)',
        'width': function(ele){ return (ele.data('conteo')/2)+10; },
        'height': function(ele){ return (ele.data('conteo')/2)+10; },
      }
    },
    {
      selector: 'edge',
      style: {
        'width': 3,
        'line-color': function (ele) { if (ele.data('tipo')=='dobla') return '#555'; else return '#ccc'; },
        'target-arrow-color': '#aae',
        'target-arrow-shape': 'triangle',
        'label': function (ele) { return ''; if (ele.data('tipo')=='dobla') return 'd'; else if (ele.data('tipo')=='enfrente') return 'e'; }
      }
    }
      ],
    layout: {
        name: 'grid',
        rows: 9
    }
    });
    cy.on('click', 'node', function(evt){
      var node = evt.target;
      alert( 'Lado: ' + node.id() );
    });
//    var layout = cy.layout({ name: 'cose'});
//    layout.run();
    function ordenar(){
        var layout = cy.layout({ name: 'cose'});
        layout.run();
    }
    ordenar();
    </script>
@endsection

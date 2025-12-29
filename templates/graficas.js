function crearGrafica(json){
    var parsed = JSON.parse(json);
    var arr = [];
    for(var x in parsed){
      arr.push(parsed[x])
    }
      return arr;
  }
  
  function crearGraficaBarra(json){
    var parsed = JSON.parse(json);
    var arr = [];
    for(var x in parsed){
      arr.push(parsed[x])
    }
      return arr;
  }
  
  function crearGraficaPie(value) {
    return value;
  }

  function crearGraficaDona(value) {
    return value;
  }
      
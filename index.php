<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <title>Enterprise Architecture ERD</title>
    <style>
        html, body, #graph {
            font-family: 'Roboto', sans-serif;
        }

        .title {
            position: absolute;
            font-size: 1.5em;
            color: rgba(50, 50, 50, .6);
            top: 45px;
            left: 130px;
            letter-spacing: .5em;
        }

        .icon {
            cursor: pointer;
        }

        .steps {
            fill: none;
            stroke-width: 2px;
        }
        .nodes{
            opacity:1;
        }
        .polymarker{
        fill:black;
        stroke: black;
        stroke-width:3px;
        }

        .panel {
            stroke-width: 2px;
        }

        text {
            user-select: none;
            font-family: 'Roboto', sans-serif;
            cursor: pointer
        }

        node {
            cursor: pointer;
        }

        svg {
            opacity: 1;
        }

        .label {
            font-size: 1.1em;
            font-weight: bold;
        }

        .legend {
            letter-spacing: .1em;
        }

        .tooltip {
            max-width: 400px;
            min-width: 200px;
            height: auto;
            position: absolute;
            top: 15%;
            left: -4000px;
            opacity: 0;
            padding: 10px;
            background: rgba(220, 220, 220, .9);
            color: black;
        }

        .tool-content {
            margin-top: 30px !important;
            z-index: 0 !important;
        }

        .close {
            background: #cacaca !important;
            border-radius: 50% !important;
            border: none !important;
            width: 30px !important;
            height: 30px !important;
            opacity: 1 !important;
        }

        .iconbox {
            border: none;
            width: 100px;
            height: 100px;
            left: -120px;
            position: absolute;
        }
    </style>
</head>

<body>
<div id="graph"></div>
<div class="tooltip"></div>
<h2 class="title">ER DIAGRAM - GRYPHON</h2>

<script src="//cdnjs.cloudflare.com/ajax/libs/d3/4.1.1/d3.min.js"></script>
<script src="js/erd_symbols.js"></script>
<script>

!(function () {
    "use strict"

    var jsonData = [];
    var data;
    var api_url = window.location.href;
    var is_fixed = 1;

    var colorsindex = ["#ff9c9c", "#ffba92", "#fedfa7", "#d5e4b2", "#b3cfd5", "#b6abd8", "#cfa7e0", "#ffb5e5", "#fff5e6"]
    var panelcolors = ["#949da9", "#f8f9fa", "#000000"];
    var textcolors = ["rgba(220,220,220,.9)", "rgba(50,50,50,.9)", "rgba(220,220,220,.9)"];

    var width = window.innerWidth
    var height = window.innerHeight

    var margin = {top: height * .1, left: 0, bottom: height * .1, right: 0}
    var chartWidth, chartHeight;

    var char_width = 10 // width of one character
    var row_height = 22;
    var er_keys = [];
    var domains = [];
    var node, node_header, link, subtext, text
    var node_data = [];
    var nodeitems;
    var items_data = [];
    var link_data = []
    var icons = ["◆ ", "▢ ", "▢ "]
    var cols = 5;
    var rows = Math.ceil(jsonData.length / cols);
    var col_spacing = (window.innerWidth * .75) / cols;
    var row_spacing = (window.innerHeight * .75) / rows;

    var line
    var svg = d3.select("#graph").append("svg")
    var simulation;

    /* var icondefs = d3.select("body").append("svg")
         .classed("iconbox", true)
         .append("defs")*/

    var tooltip = d3.select(".tooltip")
    var closebtn = d3.selectAll(".close")

    closebtn.on("click", function () {
        hidePopups();
        showTooltip()
    })
    tooltip.on("mouseleave", function () {
        showTooltip()
    })


    setSize()

    function toggle_animation() {
        console.log("toggle", is_fixed)
        is_fixed = !is_fixed

        //node.remove()
        //init()
        //simulation.nodes(data.nodes).alphaTarget(0.3).restart()
    }

    function loadIcons() {
        //setup icons for the legend


        ["refresh", "save"].forEach(function (i) {

            iconbox.append('pattern')
                .attr("patternUnits", "objectBoundingBox")
                .attr('id', function () {
                    let nm = i
                    return nm;
                })
                .attr('height', 50)
                .attr('width', 50)
                .attr('x', 0)
                .attr('y', 0)
                .append('image')
                .attr('xlink:href', function () {
                    let nm = i
                    return "/css/icons/" + nm + ".svg";
                })
                .attr('height', 50)
                .attr('width', 50)
                .attr('x', 0)
                .attr('y', 0)
        })


        iconbox.append('pattern')
            .attr("patternUnits", "objectBoundingBox")
            .attr('id', "gryphon")
            .attr('height', 80)
            .attr('width', 80)
            .attr('x', 0)
            .attr('y', 0)
            .append('image')
            .attr('xlink:href', function () {
                return "/css/icons/gryphon.svg";
            })
            .attr('height', 80)
            .attr('width', 80)
            .attr('x', 0)
            .attr('y', 0)

    }

    var iconbox = d3.select(".iconbox").select("defs")

    loadIcons()

    function showTooltip(d) {
        tooltip.selectAll("span").remove()
        tooltip.style("left", "-4000px")
            .style("opacity", 0)
        tooltip.selectAll("button").remove()

        d3.event.preventDefault();
        let pos = [d3.event.pageX, d3.event.pageY - 80];

        if (d) {

            tooltip.append("span")
                .classed(".tool-content", true)
                .html(d)

            tooltip.style("top", pos[1] + "px")
                .style("left", pos[0] + 20 + "px")

            tooltip.transition()
                .duration(4)
                .style("opacity", 1)

        } else {
            return
        }

    }

    var setColor = function (i) {
        let c = i < colorsindex.length ? i : i % colorsindex.length
        return colorsindex[c]
    }

    function setRectWidth(d) {
        return (10+ (d.node_width));
    }

    function setRectHeight(d) {
        let h = d.items.length ? d.items.length * row_height : row_height * .5
        return h + row_height * 2;
    }

    function setTextColor(d) {
        return textcolors[d]
    }

    function setBackground(d) {
        return panelcolors[d]
    }

    function setTextY(d) {
        let y = 0
        return y - margin.top;
    }

    function setRectY(d) {
        //let y = d.items ? ((d.items.length * row_height) * -.5) + 15 : -row_height
        let y = d.items ? row_height : row_height
        return y;
    }


    function factorNodes(){
        var row_num = 0
        jsonData.map(function (d, i) {
            row_num = i % cols > 0 ? row_num++ : Math.ceil(i / cols);
            var fixed_x = d.fixed_x > 0 ? (d.fixed_x * width) : (i % cols * col_spacing) + col_spacing * .5
            var fixed_y = d.fixed_y > 0 ? (d.fixed_y * height) : (row_num * row_spacing) + 2 * row_spacing
            var x = is_fixed ? fixed_x : 0;
            var y = is_fixed ? fixed_y : 0;
            Object.assign(d, {index: i, node_width:0,fixed_x: x, fixed_y: y, x: 0, y: 0, fixed: is_fixed})
        })

    }
    function factorData(){
        data = {
            nodes: node_data.map(function (d) {
                return {
                    domain: d.domain,
                    node_width: d.node_width,
                    items: d.items,
                    physical: d.physical,
                    label: d.label,
                    index: d.index,
                    status: d.status,
                    fixed: d.fixed,
                    fixed_x: d.fixed_x,
                    fixed_y: d.fixed_y,
                    descr: d.description,
                    entity_id: d.entity_id,
                    r: ~~d3.randomUniform(10, 60)()
                }
            }),
            links: link_data,
            node_items: items_data.map(function (d) {
                return {label: d.label, index: d.index,relationship:d.relationship ,r: ~~d3.randomUniform(10, 60)()}
            })
        }
    }

    function factorItems(){

        var objData = jsonData.map((d) => d);

        objData.forEach(function (d) {
            var obj = {...d}
            delete obj["items"]
            delete obj["entity"]
            obj["label"] = d["entity"]
            obj["items"] = []
            var iw = 0// width of item word
            var max_w = d["entity"].length * char_width

            if (!!d["items"]) {
                var a = d["items"].split(",")

                a.forEach(function (i) {
                    let ia = i.split(":")

                    let label = ia[1]
                    let status = ia[0]
                    let descr = ia[2]
                    iw = label.length * char_width
                    var iobj = Object()
                    iobj["label"] = label
                    iobj["status"] = status
                    iobj["descr"] = descr.replace("|", ",")
                    iobj["iskey"] = label.localeCompare("id") == 0 ? true : false


                    obj['items'].push(iobj)
                    items_data.push(iobj)
                    max_w = Math.max.apply(this, [iw, max_w])
                })

            }
            obj["node_width"] = max_w + 40
            node_data.push(obj)
        })

    }

    //return;

    function factorLinks(){
        // create links for between entities

        jsonData.forEach(function (d) {
            var de = d["entity_key"];
            var di = d["index"];
            jsonData.forEach(function (e) {
                if (e["items"] != null && d["items"] != null) {

                    var a = e["items"].toString().split(",")

                    a.forEach(function (item,i) {
                        let status_label = item.split(":")
                        let label = status_label[1];
                        let rel = status_label[3]
                        let eid = de + "_id"

                        var isvalid = !label.localeCompare(eid);
                        if (isvalid) {
                            er_keys.push(de + "_id")
                            //[{source:0,target:1,index:0,items:3},
                            var obj = Object();
                            obj["source"] = di - 0;
                            obj["target"] = e["index"];
                            obj["target_index"] = i;
                            obj["target_width"] = e["node_width"];
                            obj["target_label"] = e["entity"]
                            obj["target_relationship"] = rel

                            obj['fixed'] = is_fixed;
                            obj["domain"] = d.domain;
                            link_data.push(obj)

                            //i++
                        }
                    })
                }
            })
        })
    }


    // unique array of er keys
    er_keys = [...new Set(er_keys)]

    var load = function (jdata) {
        jsonData = jdata

        console.log(jsonData)

        domains = [...new Set(jsonData.map((d) => d.domain))];

        var eItems = jsonData.map(function (e) {
            let obj = Object;
            obj[e.entity] = e.items
        })

        var range = 3

        setSize();
        factorNodes()
        factorItems()
        factorLinks()
        factorData()

        //console.table(jsonData)
        //console.table(data.links)
        drawChart(data)
        addLegend()
        addSubtext()

    }

    function setSize() {
        width = window.innerWidth
        height = window.innerHeight
        let title_text = "ER DIAGRAM - [" + width + " " + height + "]"

        d3.select(".title")
            .html(title_text)

        let size_scale = height < 1000 || width < 1500 ? .4 : .7

        char_width = char_width * size_scale// width of one character
        row_height = 22 * size_scale

        d3.select("body").style("font-size", function () {
            return size_scale + "em";
        })

        margin = {top: height * .05, left: width * .05, bottom: height * .05, right: width * .05}
        chartWidth = width - (margin.left + margin.right)
        chartHeight = height - (margin.top + margin.bottom)

        svg.attr("viewBox", "0 0 " + width + " " + height)

        d3.select("body")
            .style("height", function () {
                return (height * 1.10) + "px"
            })

    }

    var addSubtext = function () {
        var subnodes = svg.selectAll(".subtext-node")
        var incr = row_height
        subnodes.selectAll("text").remove()

        subnodes
            .each(function (d, i) {
                let target = d3.select(this)

                if (d.items.length) {
                    d.items.forEach(function (item) {
                        let label = item.label
                        let descr = item.descr
                        let status = (er_keys.indexOf(label) > -1) ? icons[2] : icons[item.status - 0]
                        let l = status + label
                        target.append("text")
                            .classed("node-items", true)
                            .text(l)
                            .attr("x", function (d) {
                                let new_x = d.fixed ? d.fixed_x : d.x;
                                return new_x
                            })
                            .attr("y", function (d) {
                                let new_y = d.fixed ? d.fixed_y : d.y;
                                return new_y
                            })
                            //.attr("fixed",d.fixed)
                            .style("fill", function (d) {
                                return setTextColor(d.status - 0)
                            })
                            .style("font-style", function (d) {
                                return item.status - 0 == 0 ? "italic" : "none"
                            })
                            .style("text-decoration", function (d) {
                                //return  (er_keys.indexOf(label) > -1)  ? "underline": "none"
                            })

                            .style("text-anchor", "start")
                            .on("mouseover", function () {
                                showTooltip(descr)
                            })
                            .on("mouseleave", function (d) {
                                showTooltip()
                            })

                        i++
                    })

                }

            })

        nodeitems = svg.selectAll(".subtext-node")
            .selectAll(".node-items")
    }

    function addLegend() {

        //add refresh icon
        /*let refresh = svg.append("rect")
            .classed("icon", true)
            .attr("x", 0)
            .attr("y", 0)
            .attr("rx", function () {
                var r = 12
                return r
            })
            .attr("ry", function () {
                var r = 12
                return r
            })
            .attr("width", function () {
                let w = 50
                return w
            })
            .attr("height", function () {
                let h = 50
                return h
            })
            .style("fill", function () {
                return 'url(#refresh)';
            })
            .attr("transform", "translate(" + (width - 80) + ",20)")
            .on("click", function () {
                toggle_animation()
            })*/



        // add Gryphon icon
        svg.append("rect")
            .attr("x", 0)
            .attr("y", 0)
            .attr("rx", function () {
                var r = 12
                return r
            })
            .attr("ry", function () {
                var r = 12
                return r
            })
            .attr("width", function () {
                let w = 80
                return w
            })
            .attr("height", function () {
                let h = 80
                return h
            })
            .style("fill", function () {
                return 'url(#gryphon)';
            })
            .attr("transform", "translate(" + (20) + ",20)")


        var legend_w = Math.max.apply(this, [...new Set(domains.map(function (d) {
            return (d.length * char_width) + 50
        }))])
        var legend_x = (width - legend_w - 80);
        var legend_row_height = row_height * 1.5;

        var legend = svg.append("g")
            .attr("class", "legend")
            .attr("x", function () {
                return width * .95
            })
            .attr("y", function () {
                return height * .4
            })

        legend.append("rect")
            .style("fill", function (d) {
                let c = setBackground(0)
                return c
            })
            .attr("x", function () {
                return legend_x
            })
            .attr("y", function () {
                return (height * .35) - legend_row_height
            })
            .attr("rx", 4)
            .attr("ry", 4)
            .style("width", legend_w + 20)
            .style("height", function () {
                return legend_row_height + "px"
            })

        legend.append("text")
            .style("fill", function (d) {
                let c = setTextColor(1)
                return c
            })
            .text(icons[0] + "Not approved by ARB")
            .attr("transform", function (d) {
                let y = (legend_row_height + height * .35) - 2 * row_height;
                return "translate(" + (legend_x + 5) + "," + y + ")"
            })
            .style("font-style", "italic")

        legend.append("rect")
            .style("fill", function (d) {
                let c = setBackground(2)
                return c
            })
            .attr("x", function () {
                return legend_x
            })
            .attr("y", function () {
                return (height * .4) - legend_row_height
            })
            .attr("rx", 4)
            .attr("ry", 4)
            .attr("fill", "black")
            .style("width", legend_w + 20)
            .style("height", function () {
                return (domains.length * legend_row_height) + 2 * legend_row_height + "px"
            })

        legend.selectAll("drect")
            .data(domains)
            .enter()
            .append("rect")
            .style("fill", function (d) {
                let c = setColor(domains.indexOf(d))
                //return c
            })
            .attr("rx", 4)
            .attr("ry", 4)
            .style("stroke", function (d) {
                let c = setColor(domains.indexOf(d))
                return c
            })
            .style("fill", function (d) {
                let c = setColor(domains.indexOf(d))
                return c
            })
            .style("stroke-width", "2px")
            .style("width", (legend_w))
            .style("height", function () {
                return row_height + "px"
            })
            .attr("transform", function (d, i) {
                let y = i * legend_row_height + (height * .4) + row_height;
                return "translate(" + (legend_x + 10) + "," + y + ")"
            })


        legend.append("text")
            .style("fill", function (d) {
                let c = setTextColor(2)
                return c
            })
            .text("DOMAINS")
            .attr("transform", function (d) {
                let y = (legend_row_height + height * .4) - legend_row_height;
                return "translate(" + (legend_x + 20) + "," + y + ")"
            })

        legend.selectAll("dtext")
            .data(domains)
            .enter()
            .append("text")
            .style("fill", function (d) {
                let c = setTextColor(1)
                return c
            })
            .text(function (d) {
                return d
            })
            .attr("transform", function (d, i) {
                let y = i * legend_row_height + height * .4 + 1.8 * row_height;
                return "translate(" + (legend_x + 25) + "," + y + ")"
            })


    }

    function drawChart(data) {

        simulation = d3.forceSimulation()
            .force("link", d3.forceLink().id(function (d) {
                return (d.index)
            }))
            .force("collide", d3.forceCollide(function (d) {
                return (50 + (10 * d.items.length))
            }).iterations(50))
            .force("charge", d3.forceManyBody().strength(-10))
            .force("center", d3.forceCenter(width * .42, height * .58))
            .force("y", d3.forceY(0))
            .force("x", d3.forceX(0))

        // transforms line x1 y1 x2 y2 into multipoint paths
        line = d3.line()
            .x((d) => d.x)
            .y((d) => d.y + 5)
            .curve(d3.curveStep)

        link = svg.append("g")
            .attr("transform", "translate(" + margin.left + ",0)")
            .attr("class", "steps")
            .selectAll("path")
            .data(data.links)
            .enter()
            .append("path")
            .attr("d", function (d) {
                return line(d)
            })
            .style("stroke", function (d) {
                return setColor(domains.indexOf(d.domain))
            })

            .attr("marker-end", function(d){
                console.log(d)
                return 'url(#'+d.target_relationship+')'
            })
            .attr("marker-start", function(d){
                return 'url(#11)'
            })



        //node.data(data.nodes)
        node = svg.append("g")
            .attr("transform", "translate(" + [margin.left, margin.top] + ")")
            .attr("class", "nodes")
            .selectAll("rect")
            .data(data.nodes)
            .enter()
            .append("rect")
            .style("width", function (d) {
                return setRectWidth(d)
            })
            .classed("panel", true)
            .style("height", function (d) {
                return 30
            })
            .attr("fill", function (d) {
                let c = d.status - 0 == 0 ? setBackground(d.status) : setBackground(1)
                return c
            })
            .attr("stroke", function (d) {
                let c = setColor(domains.indexOf(d.domain))
                return c
            })
            .attr("rx", 4)
            .attr("ry", 4)
        //.exit()
        //.remove()

        node.call(d3.drag()
            .on("start", dragstarted)
            .on("drag", dragged)
            .on("end", dragended))


        node_header = svg.append("g")
            .attr("transform", "translate(" + [margin.left, margin.top] + ")")
            .attr("class", "nodes-header")
            .selectAll("hrect")
            .data(data.nodes)
            .enter()
            .append("rect")
            .style("width", function (d) {
                return setRectWidth(d)
            })
            .classed("label-panel", true)
            .style("height", function (d) {
                return row_height + 5
            })
            .attr("fill", function (d) {
                let c = setColor(domains.indexOf(d.domain))
                return c
            })

            .attr("rx", 0)
            .attr("ry", 0)
            .call(d3.drag()
                .on("start", dragstarted)
                .on("drag", dragged)
                .on("end", dragended))

        text = svg.append("g")
            .attr("transform", "translate(" + [margin.left, margin.top] + ")")
            .attr("class", "text")
            .selectAll("txt")
            .data(data.nodes)
            .enter()
            .append("g")
            .append("text")
            .attr("x", 0)
            .text(function (d) {
                return d.label
            })
            .classed("label", true)
            .style("fill", function (d) {
                return setTextColor(1)
            })
            .style("text-anchor", "middle")
            .style("font-style", function (d) {
                return d.status - 0 == 0 ? "italic" : "none"
            })
            .call(d3.drag()
                .on("start", dragstarted)
                .on("drag", dragged)
                .on("end", dragended));

        node_header.on("mouseover", function (d) {
            showTooltip(d.descr)
        })
        node_header.on("mouseleave", function (d) {
            showTooltip()
        })

        subtext = svg.append("g")
            .attr("transform", "translate(" + [margin.left, margin.top] + ")")
            .attr("class", "subtext")
            .selectAll("subtext")
            .data(data.nodes)
            .enter()
            .append("g")
            .classed("subtext-node", true)
            .append("text")
            .text(function (d) {
                return d.items.length ? "▢ " + d.items[0]["label"] : ""
            })
            .classed("textnode", true)
            .attr("x", 0)
            .attr("y", 0)
            .style("text-anchor", "start")
            .call(d3.drag()
                .on("start", dragstarted)
                .on("drag", dragged)
                .on("end", dragended));


        node.transition()
            //.delay(1000)
            .duration(500)
            .style("height", function (d) {
                return setRectHeight(d) + "px"
            })


        var ticked = function () {

            node
                .attr("x", function (d) {
                    let new_x = d.fixed_x && d.fixed ? d.fixed_x : d.x;
                    return new_x + (setRectWidth(d) * -.5);
                })
                .attr("y", function (d) {
                    let new_y = is_fixed ? d.fixed_y : d.y;
                    //return (new_y + setRectHeight(d) * -.5) - 80
                    return new_y - margin.top - row_height
                });


            node_header
                .attr("x", function (d) {
                    let new_x = d.fixed_x ? d.fixed_x : d.x;
                    return new_x + setRectWidth(d) * -.5;
                })
                .attr("y", function (d) {
                    let new_y = d.fixed_y ? d.fixed_y : d.y;
                    //return (new_y + setRectHeight(d) * -.5) - 80
                    return new_y - margin.top - row_height
                });

            link
                .attr("d", function (d) {
                    //console.log(d)
                    var t_mov_x = d.target.fixed_x < d.source.fixed_x  || is_fixed == 0 ? -.55 : .55
                    var t_mov_y = d.target.fixed_y < d.source.fixed_y  && is_fixed == 0 ? -.55 : 0
                    var s_mov_x = d.source.fixed_x < d.target.fixed_x || is_fixed == 0 ? -.55 : .55
                    var a = []
                    let new_sx = is_fixed ? d.source.fixed_x - (d.source.node_width*s_mov_x)- (10 * s_mov_x): d.source.x -(d.source.node_width*s_mov_x)- (10 * s_mov_x);
                    let new_sy = is_fixed ? d.source.fixed_y + (row_height*.7) : d.source.y + (row_height*.7) ;
                    let new_tx = is_fixed ? d.target.fixed_x - (d.target.node_width*t_mov_x) - (10 * t_mov_x) : d.target.x + (d.target.node_width*t_mov_x) - (10 * t_mov_x) ;
                    let new_ty = is_fixed ? d.target.fixed_y + ((d.target_index) * row_height) + row_height : d.target.y  ;
                    a.push({x: new_sx, y: new_sy});
                    a.push({x: new_tx, y: new_ty});
                    return line(a)
                })

            text

                .attr("x", function (d) {
                    let new_x = d.fixed_x ? d.fixed_x : d.x;
                    return new_x;
                    //return d.x
                })
                .attr("y", function (d) {
                    let new_y = d.fixed_y ? d.fixed_y : d.y;
                    return new_y + setTextY(d)
                });

            subtext
                .attr("x", function (d) {
                    return d.x - setRectWidth(d) * .4
                })
                .attr("y", function (d) {
                    return d.y + setTextY(d) + (row_height + 10)
                });

            if (nodeitems) {
                nodeitems
                    .attr("x", function (d) {
                        let new_x = d.fixed_x ? d.fixed_x : d.x;
                        return new_x - setRectWidth(d) * .4;
                        //return d.x - setRectWidth(d) * .4
                    })
                    .attr("y", function (d) {
                        // return d.y + setTextY(d) + row_height
                    })
                    .attr("y", function (d, i) {
                        let new_y = d.fixed_y ? d.fixed_y : d.y;
                        return new_y + (i * row_height) + setTextY(d) + row_height + 5
                    });
            }


        }

        //simulation
        //.nodes(data.node_items)
        // .on("tick",ticked)

        simulation
            .nodes(data.nodes)
            .on("tick", ticked);

        simulation.force("link")
            .links(data.links);


        function dragstarted(d) {
            d.fixed = 0;

            if (!d3.event.active) {
                simulation.alphaTarget(0.3).restart();
            }
        }

        function dragged(d) {

            if (is_fixed) {
                d.fx = d3.mouse(this)[0];
                d.fy = d3.mouse(this)[1] + 80;
                d.fixed_x = d.fx
                d.fixed_y = d.fy
            } else {
                d.fx = d3.event.x;
                d.fy = d3.event.y;
            }

        }

        function dragended(d) {
            d.fixed = 1;
            saveChanges(d)
            //simulation.stop()
        }

        setTimeout(function () {
            //simulation.stop()
            svg.transition()
                .duration(1000)
                .style("opacity", 1)
        }, 4000)

    }


    function  saveChanges (d) {
        var encode_data = (d.entity_id).concat("-").concat(d.x/width).concat("-").concat(d.y/height)

        var data_str = ("/").concat(encode_data.concat("?")) ;
        var url = api_url.replace("?", data_str );

        var xhttp = new XMLHttpRequest();
        xhttp.open("POST", url, true);
        xhttp.onreadystatechange = function () {//Call a function when the state changes.

            if (this.readyState === 4 && this.status === 200) {
                var rt = xhttp.responseText;
                console.log(xhttp.responseText)
            }
        }
        return xhttp.send();
    }
    function getJsonData(load){
        let url = "http://localhost:8888/d3-erd/erd_api.php?v=dev"

        var xhttp = new XMLHttpRequest();

        xhttp.open("GET", url, true);
        xhttp.onreadystatechange = function () {//Call a function when the state changes.

            if (this.readyState === 4 && this.status === 200 ) {
                var rt = xhttp.responseText;
                let jData = JSON.parse(xhttp.responseText)
                console.table(jData)
                if(load) {load(jData)}
            }
        }
        xhttp.send();

    }
    getJsonData(load)

}());
</script>


</body>
</html>


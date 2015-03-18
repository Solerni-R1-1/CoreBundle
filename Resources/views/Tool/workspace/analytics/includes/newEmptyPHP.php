

        <div class="slrn-statistic-wrapper">
            <legend>{{ 'mooc_subscriptions_follow_up'|trans({}, 'platform') }}</legend>
            <div id="moocSubscriptions" class="line-chart"></div>
            <div class="count_by_day_chart_legend">
                <i class="icon-hand-up"></i> <em>{{ 'Draw an area on the graph to zoom, double-click to cancel'|trans({}, 'platform') }}</em>
            </div>
        </div>
        
        <div class="slrn-statistic-wrapper">
            <legend>{{ 'mooc_workspace_interactions'|trans({}, 'platform') }}</legend>
            <div id="workspaceInteractions" class="line-chart"></div>
            <div class="count_by_day_chart_legend">
                <i class="icon-hand-up"></i> <em>{{ 'Draw an area on the graph to zoom, double-click to cancel'|trans({}, 'platform') }}</em>
            </div>
            {% if mostActiveUsers is defined and mostActiveUsers is not empty %}
            <table border="1">
	            {% for i in 0..min(9, mostActiveUsers|length - 1) %}
	            	{% set mostActiveUser = mostActiveUsers[i] %}
	           	<tr>
	            	<td>{{ mostActiveUser['user'].getFirstname() }} {{ mostActiveUser['user'].getLastname() }}</td>
	            	<td>{{ mostActiveUser['nbLogs'] }}</td>
	            </tr>
	            {% endfor %}
	        </table>
	        {% endif %}
        </div>

        <div class="slrn-statistic-wrapper">
            <legend>{{ 'mooc_forum_publication'|trans({}, 'platform') }}</legend>
            <div id="forumPublications" class="line-chart"></div>
            <div class="count_by_day_chart_legend">
                <i class="icon-hand-up"></i> <em>{{ 'Draw an area on the graph to zoom, double-click to cancel'|trans({}, 'platform') }}</em>
            </div>
            {% if forumPublishers and forumPublishers is not empty %}
            <table border="1">
	           	<tr>
	            	<th>{{ 'mooc_analytics_publisher_name'|trans({}, 'platform')}}</th>
	            	<th>{{ 'mooc_analytics_publisher_firstname'|trans({}, 'platform')}}</th>
	            	<th>{{ 'mooc_analytics_publisher_username'|trans({}, 'platform')}}</th>
	            	<th>{{ 'mooc_analytics_publisher_mail'|trans({}, 'platform')}}</th>
	            	<th>{{ 'mooc_analytics_publisher_nb_pub'|trans({}, 'platform')}}</th>
	            </tr>
	            {% for i in 0..min(9, forumPublishers|length - 1) %}
	            	{% set forumPublisher = forumPublishers[i] %}
	           	<tr>
	            	<td>{{ forumPublisher['lastname'] }}</td>
	            	<td>{{ forumPublisher['firstname'] }}</td>
	            	<td>{{ forumPublisher['username'] }}</td>
	            	<td>{{ forumPublisher['mail'] }}</td>
	            	<td>{{ forumPublisher['nbPublications'] }}</td>
	            </tr>
	            {% endfor %}
	        </table>
	        {% endif %}
	        {% if forumMostActiveSubjects is defined and forumMostActiveSubjects is not empty %}
            <table border="1">
	            {% for i in 0..min(9, forumMostActiveSubjects|length - 1) %}
	            	{% set forumMostActiveSubject = forumMostActiveSubjects[i] %}
	           	<tr>
	            	<td>{{ forumMostActiveSubject['subject'].getTitle() }}</td>
	            	<td>{{ forumMostActiveSubject['nbMessages'] }}</td>
	            </tr>
	            {% endfor %}
	        </table>
	        {% endif %}
        </div>
        
        <div class="slrn-statistic-wrapper">
            <legend>{{ 'mooc_users_activity'|trans({}, 'platform') }}</legend>
            <div id="resources-active-users-pie-chart" class="pie-chart"></div>
            <div class="count_by_day_chart_legend">
                <i class="icon-hand-up"></i> <em>{{ 'Draw an area on the graph to zoom, double-click to cancel'|trans({}, 'platform') }}</em>
            </div>
        </div>
        


<script type="text/javascript">
   
    var workspaceAccess = $.map({{ hourlyAudience[0]|json_encode|raw }}, function(value, index) {
		return [[index, value]];
    });

    var workspaceActions = $.map({{ hourlyAudience[1]|json_encode|raw }}, function(value, index) {
		return [[index, value]];
    });

    var cumulatedSubscriptions = $.map({{ subscriptionStats[0]|json_encode|raw }}, function(value, index) {
		return [value];
    });

    var dailySubscriptions = $.map({{ subscriptionStats[1]|json_encode|raw }}, function(value, index) {
		return [value];
    });

    {% if forumContributions is defined and forumContributions %}
        var forumPublications = $.map({{ forumContributions[0]|json_encode|raw }}, function(value, index) {
            return [value];
        });
        var forumsContributionMean = {{ forumContributions[1] }};
    {% else %}
        var forumPublications = {};
        var forumsContributionMean = 0;
    {% endif %}

    {% if activeUsers is defined %}
        var activeUsersData = [];
        var activeUsers = {{ activeUsers[0] }};
        var totalUsers = {{ activeUsers[1] }};
        var percentageActive = (activeUsers / totalUsers) * 100;
        activeUsersData.push(["Utilisateurs actifs", percentageActive ]);
        activeUsersData.push(["Utilisateurs non actifs", (100 - percentageActive)]);
    {% endif %}

    var bg_color = "transparent";
    if (navigator.userAgent.match(/msie/i) && navigator.userAgent.match(/8/)) bg_color = "#fff";
    var numberRows = 7;
    {% if numberRows is defined and numberRows is not empty %}
        numberRows = {{ numberRows }};
    {% endif %}
    
    $(document).ready(function() {
  
        if (workspaceAccess.length > 0 && workspaceActions.length > 0 ) {
            
            var actionsPlot2 = $.jqplot(
                'workspaceInteractions',
                [workspaceAccess, workspaceActions],
                {
                    title: {show: false},
                    grid: {
                        drawBorder: true,
                        borderWidth: 1.0,
                        shadow: false,
                        background: bg_color
                    },
                    axesDefaults: {
                        labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                        tickRenderer: $.jqplot.CanvasAxisTickRenderer
                    },
                    axes: {
                        xaxis: {                            
                            label: "Heure de la journée",
                            pad: 0,
                            min:0
                        },
                        yaxis: {
                        	label: "Nombre de connections",
                            min:0,
                            showTickMarks: true,
                            numberTicks: 5
                        },
                        y2axis: {
							label: "Activité sur le cours",
	                        min:0,
                            showTickMarks: true,
                            numberTicks: 5
                        }
                    },
                    highlighter: {
                        show: true,
                        sizeAdjust: 1,
                        tooltipOffset:12,
                        tooltipLocation:'n',
                        tooltipAxes:'xy',
                        formatString:'%s <br/> %d',
                        tooltipFadeSpeed:'fast'
                    },
                    cursor: {
                        show: true,
                        zoom: true,
                        showTooltip: false
                    },
                    seriesDefaults: {
                        showMarker:((workspaceAccess.length<10)?true:false),
                        markerOptions:{shadow:false},
                        shadow:false,
                        showLine:true,
                        useNegativeColors: false,
                        fill: true,
                        lineWidth: 1.5,
                        fillAndStroke: true,
                        fillAlpha: 0.12,
                        rendererOptions:{highlightMouseOver: true, highlightMouseDown: true}
                    },
                    series: [
                        {yaxis:'yaxis', label:'Nombre de connections'},
                        {yaxis:'y2axis', label:'Activité sur le cours'}
                    ],
                    legend: {
						show: true,
						placement: 'outsideGrid'
                    }
                }
            );
    
        }
        
        if (cumulatedSubscriptions.length>0) {
            var actionsPlot3 = $.jqplot(
                    'moocSubscriptions',
                    [cumulatedSubscriptions, dailySubscriptions],
                    {
                        title: {show: false},
                        grid: {
                            drawBorder: true,
                            borderWidth: 1.0,
                            shadow: false,
                            background: bg_color
                        },
                        axesDefaults: {
                            labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                            tickRenderer: $.jqplot.CanvasAxisTickRenderer
                        },
                        axes: {
                            xaxis: {
                            	renderer: $.jqplot.DateAxisRenderer,
                                tickOptions: {
                                    formatString:'{{ 'jqplot_date_output_format'|trans({}, 'platform') }}',
                                    showGridline: false,
                                    showMark: true,
                                    angle: -20,
                                    fontSize: '10px'
                                },
                                numberTicks:10,
                                label: "Date",
                                pad: 0
                            },
                            yaxis: {
                            	label: "Inscriptions",
                                min:0,
                                showTickMarks: true,
                                numberTicks: 5
                            }
                        },
                        highlighter: {
                            show: true,
                            sizeAdjust: 1,
                            tooltipOffset:12,
                            tooltipLocation:'n',
                            tooltipAxes:'xy',
                            formatString:'%s <br/> %d',
                            tooltipFadeSpeed:'fast'
                        },
                        cursor: {
                            show: true,
                            zoom: true,
                            showTooltip: false
                        },
                        seriesDefaults: {
                            showMarker:((cumulatedSubscriptions.length<10)?true:false),
                            markerOptions:{shadow:false},
                            shadow:false,
                            showLine:true,
                            useNegativeColors: false,
                            fill: true,
                            lineWidth: 1.5,
                            fillAndStroke: true,
                            fillAlpha: 0.12,
                            rendererOptions:{highlightMouseOver: true, highlightMouseDown: true}
                        },
                        series: [
                            {yaxis:'yaxis', label:'Inscriptions totales'},
                            {yaxis:'yaxis', label:'Inscriptions'}
                        ],
                        legend: {
    						show: true,
    						placement: 'outsideGrid'
                        }
                    }
                );
            }
            
            if (forumPublications.length>0) {
                var actionsPlot4 = $.jqplot(
                    'forumPublications',
                    [forumPublications],
                    {
                        title: {show: false},
                        grid: {
                            drawBorder: true,
                            borderWidth: 1.0,
                            shadow: false,
                            background: bg_color
                        },
                        axesDefaults: {
                            labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                            tickRenderer: $.jqplot.CanvasAxisTickRenderer
                        },
                        axes: {
                            xaxis: {
                            	renderer: $.jqplot.DateAxisRenderer,
                                tickOptions: {
                                    formatString:'{{ 'jqplot_date_output_format'|trans({}, 'platform') }}',
                                    showGridline: false,
                                    showMark: true,
                                    angle: -20,
                                    fontSize: '10px'
                                },
                                numberTicks:10,
                                label: "Date",
                                pad: 0
                            },
                            yaxis: {
                            	label: "Publications",
                                min:0,
                                showTickMarks: true,
                                numberTicks: 5
                            }
                        },
                        highlighter: {
                            show: true,
                            sizeAdjust: 1,
                            tooltipOffset:12,
                            tooltipLocation:'n',
                            tooltipAxes:'xy',
                            formatString:'%s <br/> %d',
                            tooltipFadeSpeed:'fast'
                        },
                        cursor: {
                            show: true,
                            zoom: true,
                            showTooltip: false
                        },
                        seriesDefaults: {
                            showMarker:((forumPublications.length<10)?true:false),
                            markerOptions:{shadow:false},
                            shadow:false,
                            showLine:true,
                            useNegativeColors: false,
                            fill: true,
                            lineWidth: 1.5,
                            fillAndStroke: true,
                            fillAlpha: 0.12,
                            rendererOptions:{highlightMouseOver: true, highlightMouseDown: true}
                        },
                        series: [
                            {yaxis:'yaxis', label:'Publications forum'},
                        ],
                        legend: {
    						show: true,
    						placement: 'outsideGrid'
                        },
                        canvasOverlay: {
                            show: true,
                            objects: [
                                {dashedHorizontalLine: {
                                    name: 'average',
                                    y: forumsContributionMean, 
                                    lineWidth: 1,
                                    color: 'red',
                                    shadow: false
                                }}
                            ]
                        }
                        
                    }
                );
            }
            
            if (activeUsersData.length>0) {
                var activeUsersResourcesPlot = $.jqplot(
                    'resources-active-users-pie-chart',
                    [activeUsersData],
                    {
                        title: {show: false},
                        grid: {
                            drawBorder: false,
                            shadow: false,
                            background: bg_color,
                            useNegativeColors: false
                        },
                        highlighter: {
                            show: false
                        },
                        cursor: {
                            show: false,
                            zoom: false,
                            showTooltip: false
                        },
                        seriesDefaults: {
                            showMarker:true,
                            renderer:$.jqplot.PieRenderer,
                            rendererOptions:{
                                showDataLabels: true,
                                dataLabelThreshold: 2,
                                dataLabels: 'percent',
                                sliceMargin:0.3,
                                dataLabelFormatString: '%.1f%%',
                                highlightMouseOver:false
                            },
                            shadow:false
                        },
                        legend:{
                            location: 's',
                            border:'none',
                            renderer: $.jqplot.CavasTextRenderer,
                            show:true,
                            showMarker:true,
                            rendererOptions:{
                                numberRows:numberRows
                            },
                            backgroundColor:bg_color,
                            placement:'outsideGrid'
                        }
                    }
            );
            
        }        
        
    });
</script>


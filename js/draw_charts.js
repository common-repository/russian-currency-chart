( function( $ ) {
    
    $( document ).ready( function() {

        function render_chart( currency='usd', update=false ) { 

            render_chart.cur_cur = currency;
            currency_data = currency == "usd" ? curr_data.usd_data : curr_data.eur_data;

            if ( update ) {
                render_chart.chrt.data.datasets[0].data = currency_data;
                render_chart.chrt.options.tooltips.callbacks.label = function( tooltipItem, data ) {
                    return ( currency == "usd" ? "USD: " : "EUR: " ) + tooltipItem.yLabel;
                };
                render_chart.chrt.update();
                return;
            }

            let chart_settings = {
                type: 'line',
                data: {
                    labels: curr_data.labels,
                    datasets: [{
                        data: currency_data,
                        fill: false,
                        borderColor: curr_data.chart_color,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointBackgroundColor: curr_data.chart_color,
                        },
                    ]
                },
                options: {
                    legend: {
                        display: false,
                    },
                    tooltips: {
                        mode: "x-axis",
                        displayColors: false,
                        titleFontSize: 10,
                        bodyFontSize: 10,
                        xPadding: 8,
                        yPadding: 8,
                        callbacks: {
                            label: function( tooltipItem, data ) {
                                return ( currency == "usd" ? "USD: " : "EUR: " ) + tooltipItem.yLabel;
                            },
                        },
                    },
                    responsive: true,
                    elements: {
                        line: {
                            tension: 0,
                        },
                    },
                    scales: {
                        xAxes: [{
                            gridLines: {
                                color: "rgba(0, 0, 0, 0)",
                            },
                        }],
                        yAxes: [{
                            gridLines: {
                                drawBorder: false,
                            },
                            ticks: {
                                maxTicksLimit: 6,
                            },
                        }],
                    }
                },
            };
            Chart.defaults.global.defaultFontSize = '9';

            let ctx = document.getElementById("ru-currency-chart-id");
            render_chart.chrt = new Chart(ctx, chart_settings );

        }
        
        curr_data.show_by_def == "1" && render_chart();

        $( '.ru-currency-chart-draw_icon' ).click( function() {

            if ( $( this ).hasClass( "on" ) ) {

                $( this ).removeClass( "on" );
                $( '.ru-currency-chart_daily' ).removeClass( 'current' );
                $( "#ru-currency-chart-id" ).css( "display", "none" );

            } else {

                $( '#ru-currency-chart-id' ).hasClass( 'chartjs-render-monitor' ) || render_chart(); 

                $( ".ru-currency-chart_daily" ).each( function( i, e ) {
                    $( this ).data( 'currency' ) == render_chart.cur_cur && $( this ).addClass( "current" );
                });

                $( this ).addClass( "on" );
                $( "#ru-currency-chart-id" ).css( "display", "block" );

            }

        } );

        $( '.ru-currency-chart_daily' ).click( function() {
                if ( $( "#ru-currency-chart-id" ).css( "display" ) == "none" ) {
                    return;
                }

                if ( $( this ).data( 'currency' ) == 'usd' ) {
                    render_chart( "usd", true );
                } else if ( $( this ).data( 'currency' ) == 'eur' ) {
                    render_chart( "eur", true );
                }

                $( '.ru-currency-chart_daily' ).removeClass( "current" );
                $( this ).addClass( "current" );
        });
    });
}) ( jQuery );

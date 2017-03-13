/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPLv3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v0.9.9-beta
 *
 * Description: Code for Analytics chart
*/

(function ($) 
{
	$.fn.extend(
	{
        	scatter: function (options) 
		{
            		var settings = $.extend(
			{
				top: 53,
				bottom: 73,
				right: 5,
				left: 210,
                		xLabel: '',
                		yLabel: '',
                		rows: 1,
                		columns: 1,
                		subsections: 0,
                		color: '#CCC',
                		responsive: true,
                		xUnits: [],
                		yUnits: []
            		}, options);

            		return this.each(function () 
			{
      				var chart = $(this),
                    		colWidth, rowHeight,
                    		xLabel, yLabel,
                    		xUnits, yUnits;
                		chart.addClass('scatterplot');

                		if (settings.width < 1) 
				{
                    			settings.width = 0;
                		}
                		if (settings.height < 1) 
				{
                    			settings.height = 0;
                		}
                		if (settings.rows < 1) 
				{
                    			settings.rows = 1;
                		}
                		if (settings.columns < 1) 
				{
                    			settings.columns = 1;
                		}
                		if (settings.subsections < 1) 
				{
                    			settings.subsections = 1;
                		}

                		if (settings.responsive) 
				{
                    			chart.addClass('responsive');
                    			colWidth = (1 / settings.columns) * 100;
                    			rowHeight = (1 / settings.rows) * 100;
                		} 
				else 
				{
                    			colWidth = settings.width / settings.columns;
                    			rowHeight = settings.height / settings.rows;
                		}

                		chart.css(
				{
                    			position: 'fixed',
                    			top: settings.top,
                    			bottom: settings.bottom,
		    			right: settings.right,
 		    			left: settings.left,
                    			backgroundColor: settings.color,
                    			margin: 40,
                    			backgroundImage: 'linear-gradient(white 2px, transparent 2px), linear-gradient(90deg, white 2px, transparent 2px), linear-gradient(rgba(255,255,255,.4) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.4) 1px, transparent 1px)',
                    			backgroundPosition: '-2px -2px, -2px -2px, -1px -1px, -1px -1px'
                		});

                		if (settings.responsive) 
				{
                    			chart.css(
					{
                        			backgroundSize: colWidth + '% ' + rowHeight + '%,' + colWidth + '% ' + rowHeight + '%,' + (colWidth / settings.subsections) + '% ' + (rowHeight / settings.subsections) + '%,' + (colWidth / settings.subsections) + '% ' + (rowHeight / settings.subsections) + '%'
                    			});
                		} 
				else 
				{
                    			chart.css(
					{
                        			backgroundSize: colWidth + 'px ' + rowHeight + 'px,' + colWidth + 'px ' + rowHeight + 'px,' + (colWidth / settings.subsections) + 'px ' + (rowHeight / settings.subsections) + 'px,' + (colWidth / settings.subsections) + 'px ' + (rowHeight / settings.subsections) + 'px'
                    			});
                		}

                		if (settings.xUnits.length > 0) 
				{
                    			chart.append('<span class="x-Units"><ul></ul></span>');
                    			xUnits = chart.find('.x-Units ul');
                    			
					$(settings.xUnits).each(function () 
					{
                        			xUnits.append('<li>' + this + '</li>');
                    			});
                
    					xUnits.find('li').width(1 / (settings.xUnits.length - 1) * 100 + '%');
                    			
					chart.find('.x-Units').css(
					{
                        			bottom: -xUnits.height()
                    			});
                		}

                		if (settings.yUnits.length > 0) 
				{
                    			chart.append('<span class="y-Units"><ul></ul></span>');
                    			yUnits = chart.find('.y-Units ul');
                    			
					$(settings.yUnits).each(function () 
					{
                        			yUnits.append('<li>' + this + '</li>');
                    			});
                    
					yUnits.find('li').width(1 / (settings.yUnits.length - 1) * 100 + '%');
                   
					chart.find('.y-Units').css(
					{
                        			width: chart.height(),
                        			left: -(chart.height() / 2 - yUnits.height() / 2) - yUnits.height(),
                        			bottom: chart.height() / 2 - yUnits.height() / 2
                    			});
                		}

                		if (settings.xLabel.length > 0) 
				{
                    			chart.append('<span class="x-Label"></span>');
                    			xLabel = chart.find('.x-Label').html(settings.xLabel);
                    
					xLabel.css(
					{
                        			left: chart.width() / 2 - xLabel.width() / 2,
                        			bottom: -(xLabel.height() + chart.find('.x-Units').height())
                    			});

                    			if (settings.responsive) 
					{
                        			xLabel.css(
						{
                            				left: (chart.width() / 2 - xLabel.width() / 2) / chart.width() * 100 + '%'
                        			});
                    			}
                		}
              
			  	if (settings.yLabel.length > 0) 
				{
                    			chart.append('<span class="y-Label"></span>');
                    			yLabel = chart.find('.y-Label').html(settings.yLabel);
                    
					yLabel.css(
					{
                        			left: -(60 + chart.find('.y-Units').height()),
                        			bottom: chart.height() / 2 - yLabel.height() / 2
                    			});
                    
					if (settings.responsive) 
					{
                        			yLabel.css(
						{
                            				bottom: (chart.height() / 2 - yLabel.height() / 2) / chart.height() * 100 + '%'
                        			});
                    			}
                		}

                		if (settings.xUnits.length > 0) 
				{
                    			chart.css(
					{
                        			marginBottom: 40 + chart.find('.x-Units').height()
                    			});
                		}
                
				if (settings.yUnits.length > 0) 
				{
                    			chart.css(
					{
                        			marginLeft: 40 + chart.find('.y-Units').height()
                    			});
                		}
            		});
        	},

        	plot: function (options) 
		{
            		var settings = $.extend(
			{
                		xPos: 0,
                		yPos: 0,
                		radius: 20,
                		color: 'rgb(255, 0, 0)',
                		responsive: false
            		}, options);

            		return this.each(function () 
			{
                		var point = $(this),
                    		left = -1,
                    		bottom = -1;
                		settings.responsive = point.parent().hasClass('responsive');

                		left = point.css('left');
                		bottom = point.css('bottom');

                		if (settings.xPos < 0) 
				{
                    			settings.xPos = 0;
                		}
               
				if (settings.yPos < 0) 
				{
                    			settings.yPos = 0;
                		}
                
				if (settings.radius < 1) 
				{
                    			settings.radius = 1;
                		}

                		point.addClass('point');

                		if (point.width() > 0) 
				{
                    			settings.radius = point.width() / 2;
                		}
	
               	 		if (point.css('background-color') === settings.color || point.css('background-color') === 'transparent') 
				{
                    			point.css(
					{
                        			backgroundColor: settings.color
                    			});
                		}
              
  				point.css(
				{
                    			width: settings.radius * 2,
                    			height: settings.radius * 2,
                    			borderRadius: settings.radius
                		});

                		if (settings.responsive) 
				{
                    			if (typeof settings.xPos === 'string' || settings.xPos instanceof String) 
					{
                        			if (settings.xPos.indexOf('%') !== -1) 
						{
                            				point.css(
							{
                                				left: ((parseFloat(settings.xPos) / 100) - (settings.radius / point.parent().width())) * 100 + '%'
                            				});
                        			} 
						else 
						{
                            				point.css(
							{
                                				left: settings.xPos
                            				});
                        			}

                    			} 
					else if (typeof left === 'string' || left instanceof String) 
					{
                        			if (left.indexOf('%') !== -1) 
						{
                            				point.css(
							{
                                				left: ((parseFloat(left) / 100) - (settings.radius / point.parent().width())) * 100 + '%'
                            				});
                        			} 
						else 
						{
                            				point.css(
							{
                                				left: left
                            				});
                        			}
                    			} 
					else 
					{
                        			point.css(
						{
                            				left: settings.xPos
                        			});
                    			}

                    			if (typeof settings.yPos === 'string' || settings.yPos instanceof String) 
					{
                        			if (settings.yPos.indexOf('%') !== -1) 
						{
                            				point.css(
							{
                                				bottom: ((parseFloat(settings.yPos) / 100) - (settings.radius / point.parent().width())) * 100 + '%'
                            				});
                        			} 
						else 
						{
	 	                   			point.css(
							{
                                				bottom: settings.yPos
                            				});
                        			}
                    			} 
					else if (typeof bottom === 'string' || bottom instanceof String) 
					{
                        			if (bottom.indexOf('%') !== -1) 
						{
                            				point.css(
							{
                                				bottom: ((parseFloat(bottom) / 100) - (settings.radius / point.parent().width())) * 100 + '%'
                            				});
                        			} 
						else 
						{
                            				point.css(
							{
                                				bottom: bottom
                            				});
                        			}
                    			} 
					else 
					{
                        			point.css(
						{
                            				bottom: settings.yPos
                        			});
                    			}
                		} 
				else 
				{
                    			left = parseInt(left, 10);
                    			bottom = parseInt(bottom, 10);
                  
				  	if (left !== -1 && !isNaN(left)) 
					{
                        			point.css(
						{
                            				left: left - settings.radius
                        			});
                    			} 
					else 
					{
                        			point.css(
						{
                            				left: settings.xPos - settings.radius
                        			});
                    			}
                 
   					if (bottom !== -1 && !isNaN(bottom)) 
					{
                        			point.css(
						{
                            				bottom: bottom - settings.radius
                        			});
                    			} 
					else 
					{
                        			point.css(
						{
                            				bottom: settings.yPos - settings.radius
                        			});
                    			}
                		}
            		});
        	}
    	});
})(jQuery);

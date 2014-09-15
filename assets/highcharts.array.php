<?php
echo "
<script type='text/javascript'>
	$(function () 
	{
		Highcharts.setOptions(
		{
				global : 
				{
					useUTC : false
				}
		});
		
		$('#container').highcharts
		({
			chart: 
			{        
				renderTo: 'container',
				defaultSeriesType: 'line',
				zoomType: 'x',
			},
			title: { text: '".$group." Freezers <br>Location: ".$loc."<br>".$hours." Hour View | 1/".$skip." Density'},
			subtitle: { text: ''},
			
			xAxis: 
			{
				type: 'datetime',
				dateTimeLabelFormats: 
				{
					hour: '%H:%M',
					day: '%A',
					week: '%A',
					month: '%B',
					year: '%Y'
				},
			},
			yAxis: 
			{
				title: 
				{
					text: 'Temperature'
				},
				labels: 
				{
					formatter: function() 
					{
						return this.value / 1 +'°C';
					}
				}
			},
			tooltip: 
			{
				formatter: function() 
				{
					return  '<b>' + this.series.name +'</b><br/>' +
					Highcharts.dateFormat('%H:%M', new Date(this.x))
					+ ' | ' + this.y + ' °C';
				},
				pointFormat: '{series.name} reported <b>{point.y:,.0f}°C</b><br/>at {point.x}'
			},
			plotOptions: 
			{
					line: 
					{
						lineWidth: 6,
						marker: 
						{
							enabled: false
						}
					}
			},
			series: 
			[
				{
					name: '" . $freezer_array['name'][$index] . "<br>" . $freezer_array['location'][$index] . "',
					color: '#" . $freezer_array['color'][$index]."
					, dashStyle: 'ShortDash',".
					"data: 
					{
						x: [".join($chart['Time'], ',')."],
						y: ["join($chart[$freezer_array['id'][$index]] ',')."]     
					}
				}".
				
			
			/*
				$index = 0;
				/* Loop through freezers and prepare data */
			/*
				foreach ($freezer_array['id'] as $id)
				{
					echo"
					{
						name: '" . $freezer_array['name'][$index] . "<br>" . $freezer_array['location'][$index] . "',
						color: '#" . $freezer_array['color'][$index];
						/* Define each freezer graph */
			/*			
						echo "'
						, dashStyle: 'ShortDash',".
							/*pointInterval: ".$skip." * 60 * 1000,*/
			/*
						"data: 
						{
							x: [".join($chart['Time'], ",")."]
							y: ["join($chart[$freezer_array['id'][$index]], ",")."]      
						}";
			
						echo "
					},";
					$index ++
				}
			*/
			"
			]            
		}); 
	});
</script>";
            
?>
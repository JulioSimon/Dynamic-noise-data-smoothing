<?php

public function RawDataSmoothing (array $raw_data, $percentage = 20, $breakingDetection = 20, $breakingDetectionCalibration = 100){
		
	// Array with the Smoothed Data
	$data_smoothed = array();
	
	// Get the amount of data
	$data_amount = count($raw_data);
	
	// Data Smoothing Zone
	$data_smoothingzone = round((($percentage / 100) * $data_amount));
	
	/*
	/* BreakingDetection FILTER BEGIN
	/*
	/* Detect breaking zones and save the spots 
	/* that have vital information not to be smoothed.
	///////////////////////////////////////////////////////////////*/
	
	// Get the moving average zone based on the smoothing percentage
	// FOR THE FILTER ONLY
	$data_smoothingzone_filter = ($data_smoothingzone > $breakingDetectionCalibration ? $breakingDetectionCalibration : $data_smoothingzone);
	
	// Detected breaking zones array inicialization
	$breakingZones = array();
	
	// Run along the raw_data to check posible breaking zones
	for($i = 1; $i < count($raw_data); $i++){
		
		// Initialize the elements to compare
		$past_element = $raw_data[$i - 1];
		$today_element = $raw_data[$i];
		
		// Check if there is a posible breaking zone acording with the breakingDetection
		if(abs($past_element - $today_element) > $breakingDetection){
							
			// Check if the breaking zone continues
			// acording with the breakingDetection
			$posible_breaking_zone = true;
			$posible_breaking_zone_index = $i; // Todays Index Element as I always will compare from the past element
			$posible_breaking_zone_limit = (($i + $data_smoothingzone_filter) >= count($raw_data) ? count($raw_data) : $i + $data_smoothingzone_filter);
			
			
			for($k = $i; $k < $posible_breaking_zone_limit; $k++){
														
				if(abs($past_element - $raw_data[$posible_breaking_zone_index]) < $breakingDetection){
					
					$posible_breaking_zone = false;
					
					// Advance the loop right after the fake breaking zone!!
					$i = $k;
											
					// Exit this loop if the posible breaking zone was a fake.
					break;
				}
				
				$posible_breaking_zone_index++;
				
			}
			
			// If it is a posible breaking zone that continues after
			// analize the inmediate next values of the past element.
			if($posible_breaking_zone){
								
				// Posible breaking zone detection, lets calculate the surrounding
				// average ranges to check if its a puntual noise or a vital information.
				$subgroup_semirange_count = ($data_smoothingzone_filter % 2 == 0 ? $data_smoothingzone_filter / 2 : round($data_smoothingzone_filter / 2, 0, PHP_ROUND_HALF_DOWN));
				
				// LEFT ZONE
				$left_subgroup_range_min = ((($i - 1) - $subgroup_semirange_count) < 0 ? 0 : ($i - 1) - $subgroup_semirange_count);
				$left_subgroup_range_max = $i - 1;
				$left_subgroup_pre_average = $past_element;
				
				// Calculate the average of the left subgroup
				for ($j = $left_subgroup_range_min; $j <= $left_subgroup_range_max; $j++){
					
					$left_subgroup_pre_average = $left_subgroup_pre_average + $raw_data[$j];
					
				}
				
				$left_subgroup_average = round($left_subgroup_pre_average / count(range($left_subgroup_range_min, $left_subgroup_range_max)));
				
				// RIGHT ZONE
				$right_subgroup_range_min = $i;
				$right_subgroup_range_max = (($i + $subgroup_semirange_count) > $data_amount - 1 ? $data_amount - 1 : $i + $subgroup_semirange_count);
				$right_subgroup_pre_average = $today_element;
				
				// Calculate the average of the right subgroup
				for ($j = $right_subgroup_range_min; $j <= $right_subgroup_range_max; $j++){
					
					$right_subgroup_pre_average = $right_subgroup_pre_average + $raw_data[$j];
					
				}
				
				$right_subgroup_average = round($right_subgroup_pre_average / count(range($right_subgroup_range_min, $right_subgroup_range_max)));
				
				// Determine if the posible breaking zone is a vital information
				// acording with the surrounding average calculations
				if(abs($left_subgroup_average - $right_subgroup_average) > $breakingDetection){
					
					// It is vital information, lets save the PAST ELEMENT INDEX into the array
					$breakingZones[] = array("Position" => $i - 1, "Value Past" => $raw_data[$i - 1], "Value Today" => $raw_data[$i], "SmoothingZone" => $data_smoothingzone_filter, array("Left Average" => $left_subgroup_average, "Left Range Min" => $left_subgroup_range_min, "Left Range Max" => $left_subgroup_range_max), array("Right Average" => $right_subgroup_average, "Right Range Min" => $right_subgroup_range_min, "Right Range Max" => $right_subgroup_range_max));
					
				}
				
			}
			
		}
		
	}
	
	// Split the array delimited by the vital breaking zones and 
	// smooth each section, after that, combine the smoothed sections
	// in order to get our full raw smoothed data keeping the vital breaking zones.
	$slicing_indexes = array();
	$last_value = 0;
	
	// First, get the indexes for slicing.
	foreach($breakingZones as $value){
			
		$slicing_indexes[] = array("Start" => $last_value, "End" => $value["Position"]);
		$last_value = $value["Position"] + 1;

	}
	$slicing_indexes[] = array("Start" => $last_value, "End" => count($raw_data));
	
	// Second, slice the raw_data array.
	$sliced_data = array();
	
	for($x = 0; $x < count($slicing_indexes); $x++){
		
		$sliced_data[$x] = array_slice($raw_data, $slicing_indexes[$x]["Start"], (($slicing_indexes[$x]["End"] + 1) - $slicing_indexes[$x]["Start"]));
		
	}
	
	// Third, smooth every slice.
	$smoothed_sliced_data = array();
	
	for($z = 0; $z < count($sliced_data); $z++){
		
		// Data Smoothed
		$data_smoothed = array();
	
		// Slice data amount
		$data_inner_amount = count($sliced_data[$z]);
								
		// Slice data Smoothing Zone
		$data_inner_smoothingzone = round((($percentage / 100) * $data_inner_amount));			
	
		// Calculate the moving average of every subgroup of elements
		// based on the smoothingzone.
		for ($i = 0; $i < $data_inner_amount; $i++){
			
			$subgroup_average = 0;
			$subgroup_range_min = 0;
			$subgroup_range_max = 0;
			
			// Determine the local smoothing range
			// and correct the outranged data
			$subgroup_semirange_count = ($data_inner_smoothingzone % 2 == 0 ? $data_inner_smoothingzone / 2 : round($data_inner_smoothingzone / 2, 0, PHP_ROUND_HALF_DOWN));
			$subgroup_range_min = (($i - $subgroup_semirange_count) < 0 ? 0 : $i - $subgroup_semirange_count);
			$subgroup_range_max = (($i + $subgroup_semirange_count) > $data_inner_amount - 1 ? $data_inner_amount - 1 : $i + $subgroup_semirange_count);
			
			// Calculate the average of the subgroup
			for ($j = $subgroup_range_min; $j <= $subgroup_range_max; $j++){
				
				$subgroup_average = $subgroup_average + $sliced_data[$z][$j];
				
			}
			
			$subgroup_average = round($subgroup_average / count(range($subgroup_range_min, $subgroup_range_max)));
			
			// Asign the subgroup_average to the data_smoothed
			$data_smoothed[$i] = $subgroup_average;
			
		}
		
		// Return the sliced Array with the Smoothed Data
		$smoothed_sliced_array[] = $data_smoothed;
		
	}
	
	
	// Forth, combine the smoothed sliced arrays in order
	// to get the final smoothed array
	$final_smoothed_array = array();
	
	$final_smoothed_array = call_user_func_array('array_merge', $smoothed_sliced_array);
	
	// Fith, return the final array
	return $final_smoothed_array;
}

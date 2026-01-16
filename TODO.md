# TODO - RadioOptionModal Damage Options Fix

## Completed Tasks
- [x] Fixed damage options accumulation in RadioOptionModal.vue
  - Modified `getPrioritySettings()` function to accumulate damage_options from all selected radio options
  - Added logic to combine damage options from multiple selections and remove duplicates
  - Now when multiple radio options are selected, their damage options will appear together in the textarea

## Summary
The issue was that damage options (data kerusakan) were not accumulating correctly from multiple selected radio options. The original code was only taking damage options from the first selected option instead of combining them from all selected options. The fix ensures that when option A has damages A,B and option B has damages C,D, selecting both will show all damages A,B,C,D in the textarea.

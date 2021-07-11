## 1.1.1 (July 11, 2021)
  - bumped min compose version to v1.1.7
  - now allowing user candidates to visit Core-only pages
  - added more robot images

## 1.1.0 (January 21, 2021)
  - bumped compose dependency version
  - added DB21M robot image
  - added trimetric renderings for robots
  - renamed DB21 -> DB21M
  - added thumbnail for DB21
  - fixed robot thumbnail image

## 1.0.4 (October 18, 2020)
  - removed dependency on ROS package, moved to package `duckietown_ros` instead. cleaned up old images for duckietown editor. cleaned up README.md.

## 1.0.3 (October 18, 2020)
  - moved stuff relative to DCSS to a separate package

## 1.0.2 (September 24, 2020)
  - moved `desktop` page to duckietown_duckiebot package
  - fixed integration with WordPress API

## 1.0.1 (August 11, 2020)
  - added favicon
  - implemented communication with WordPress API to fetch user info
  - moved pages and front-end stuff to duckietown-dashboard package
  - removed old modules

## 1.0.0 (July 23, 2020)
  - fixed bug
  - minor

## 1.0.0-rc2 (July 21, 2020)
  - added compatibility data to metadata.json as per compose v1.0+ requirement
  - reformatted metadata.json
  - preparing transition to compose v1.0

## 1.0.0-rc (July 02, 2020)
  - added library vis.js
  - added support for non-string health properties in diagnostics page
  - added WT19B robot image
  - renamed thumbnails image for robots
  - diagnostics: added CPU frequency plot
  - added wide-mode switch to diagnostics page
  - increased max temp to 90'C in diagnostics tab
  - Merge branch 'fix-mission-control'
  - added automatic creation of the group "Anybody in Duckietown".
  - fixed Twist2D and WheelsCmd block renderers
  - only administrator can now grant and revoke storage permissions
  - format login module
  - improved cloud_storage page
  - added cloud_storage page
  - implemented duckietown storage permissions back-end
  - uid and gid in API service duckietown_storage are now nullable
  - added storage permission management implementation in Duckietown.php
  - added duckietown_storage API
  - minor
  - moved tile images
  - removed old robot types thumbnails
  - added robot types thumbnails
  - renamed page setup-token -> onboarding
  - added download button for logs in diagnostics page
  - added download script for diagnostics logs
  - removed unused code
  - minor
  - updated plots legend to show Group and Subgroup in Diagnostics page
  - fixed bug in colors non updating when a log is removed
  - renamed Type -> Subgroup
  - added duckiebot, watchtower, greenstation images
  - added swap plot to resources tab
  - minor
  - improved diagnostics tabs
  - Merge remote-tracking branch 'origin/master'
  - minor
  - improved diagnostics tabs
  - fixed bug
  - Merge branch 'bug-fix'
  - pcpu, pmem, and nthreads plots are now merged in processes tab
  - improved diagnostics tabs
  - improved diagnostics tables
  - diagnostics: improved tabs; added progress bar; added external host
  - added configuration to package
  - improved diagnostics tabs
  - implemented health tab; implemented queue of Ajax jobs
  - improved diagnostics tabs
  - refactoring
  - refactoring
  - implemented diagnostics/tab:system
  - clean up Duckietown.php
  - added diagnostics page
  - Cleaned up Duckietown.php
  - Merge branch 'master' of github.com:afdaniele/compose-pkg-duckietown
  - minor
  - devel

## 0.1.5 (January 27, 2020)
  - fixed bug

## 0.1.4 (January 08, 2020)
  - now recording login system
  - removed default value from Core::getSetting calls
  - added Get your token to Duckietown Login modal
  - removed Last modified time meta

## 0.1.3 (April 24, 2019)
  - removed JS libraries that are now available in \compose

## 0.1.2 (April 21, 2019)
  - minor

## 0.1.1 (April 21, 2019)
  - added bump-version
  - added support for redirect-after-login
  - minor
  - added error codes

## 1.0.0 (April 21, 2019)
  - added support for redirect-after-login
  - minor
  - added error codes
  - added profile add-on
  - renamed page setup -> setup-token
  - removed pages from dt17
  - minor
  - added local token verifier
  - added login-with-duckietoken
  - added offline token verification
  - refactored code
  - added portainer as dependency
  - cleared config file
  - minor
  - fixed bug; formatted code
  - added dependency package
  - moved ROS-related stuff to ros package
  - minor
  - challenges API v2->v3
  - renamed db from token to authentication
  - Merge branch 'master' of https://github.com/afdaniele/compose-pkg-duckietown
  - fixed minor bug
  - added setup page; updated access level in pages
  - added duckietown API endpoints
  - removed Dashboard page
  - added base58 JS lib
  - added public logo
  - removed  property from API endpoint config
  - Merge branch 'master' of https://github.com/afdaniele/compose-pkg-duckietown
  - minor
  - added block renderers
  - minor
  - Duckiebot page redesigned
  - new JS libraries added
  - disabled 'candidate' user registration
  - minor
  - Update README.md
  - Update README.md
  - documentation updated
  - minor changes
  - minor
  - duckiebot->storage api now provides info about size of device
  - duckiebot page shows message when bot is not online
  - duckiebot page now visible to admin and superv as well
  - documentation about network bridging added
  - Duckieboard now takes settings from the Core rather than Configuration
  - Duckietown page added
  - jquery UI added as package-specific JS
  - empty tile added
  - the page Duckiebot is now only accessible by User (out Admin and Supervisor)
  - ignore configuration.json according to policy in v0.8
  - WK images added
  - trashcan image added
  - duckietown town builder wip
  - DS_Store removed
  - Duckietown main module updated
  - page metadata updated to reflect new convention for icon class (type => class)
  - duckietown creator page WIP added
  - duckietown creator toolbox tiles images added
  - minor updates
  - package first commit. moved from compose main repo
  - Update README.md
  - Initial commit

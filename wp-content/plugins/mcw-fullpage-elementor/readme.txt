=== FullPage for Elementor ===
Contributors: meceware, Álvaro Trigo
Tags: fullpage, full page, full screen, fullpage.js, one page, onepage, presentation, scrolling, slider, slide, slideshow, swipe, elementor, wordpress
Requires at least: 6.0.0
Tested up to: 6.2.2
Requires PHP: 5.6.20
Stable Tag: 2.0.10
License: Commercial

This plugin simplifies creation of fullscreen scrolling websites with WordPress/Elementor and saves you big time.

== Description ==

This plugin simplifies creation of fullscreen scrolling websites with WordPress/Elementor and saves you big time.

[Documentation](https://www.meceware.com/docs/fullpage-for-elementor/)

= Top Features =
* Fully responsive.
* Touch support for mobiles, tablets, touch screen computers.
* Easy adding sections and slides.
* Full page scroll with optional visible scrollbar.
* Optional Auto-height sections.
* CSS3 and (optional) JS animations.
* Animated anchor links.
* Optional show/hide anchor links in the address bar.
* Optional vertically centered row content.
* Optional section and slide loops.
* Optional section only scrollbars.
* Optional keyboard support while scrolling.
* Optional history record. When this is enabled, browser back button will go to the previous section.
* Optional horizontal and vertical navigation bars with different styles.
* Optional responsive scrollbar. When responsive width and height given, a normal scroll page will be used under the given width and height values.
* Optional empty page template or supply your own page template
* CSS and JS minified.

= How To Use =

For the full documentation visit the [documentation site](https://www.meceware.com/docs/fullpage-for-elementor/)

* Create a new page/post.
* Add your content in Elementor sections. Each section is defined as FullPage section as well.
* To create slides, add Elementor Inner Section widgets inside sections. Each Inner Section widget is defined as slides if `Is Slides` option of the section is enabled.
* Adjust parameters of the sections.
* Adjust FullPage parameters from Page Settings.

= Credits =

Thanks to [Álvaro Trigo](https://www.alvarotrigo.com/fullpage/) for awesome fullpage.js plugin.

== FAQ ==

No FAQ yet.

== Screenshots ==

Please check the [documentation](https://www.meceware.com/docs/fullpage-for-elementor/) for screenshots.

== Changelog ==

**v2.0.10**

  - Fullpage.js library is updated to v4.0.20.

**v2.0.9**

  - Fullpage.js library is updated to v4.0.19.

**v2.0.8**

  - Fullpage.js library is updated to v4.0.18.
  - Minor improvements.

**v2.0.7**

  - Fullpage.js library is updated to v4.0.17.

**v2.0.6**

  - Fullpage.js library is updated to v4.0.16.
  - Minor bug fixes and improvements.

**v2.0.5**

  - Fullpage.js library is updated to v4.0.15.
  - Fix: A bug fix with responsive mode and full height columns.

**v2.0.4**

  - Fix: A bug fix with the columns for slides.

**v2.0.3**

  - Fix: A bug fix with the columns for slides.
  - Fix: Fixed extensions not working when not activated.

**v2.0.2**

  - Fullpage.js library is updated to v4.0.14.

**v2.0.1**

  - FIX: `Is Header A Section` option is added back.
  - FIX: Header takes up full height bug is fixed.
  - Translation files are added under `languages` folder.

**v2.0.0**

  - Fullpage.js is updated to v4.0.12.
    - BREAKING: 'Easing' option is changed. Instead 'Easing CSS' option is created and CSS/JS easing options are updated.
    - BREAKING: 'Top' and 'Bottom' options are removed from 'Vertical Alignment' option. Instead 'Default' option can be selected and vertical alignment can be adjusted from Elementor section options.
    - BREAKING: Data-Centered option for Offset Sections is updated. New options are added.
    - BREAKING: Customized scripts and styles added by support or manually should be checked.
    - 'Scroll Overflow Options' options ('Show Scroll Overflow Scrollbars', 'Fade Scroll Overflow Scrollbars' and 'Interactive Scroll Overflow Scrollbars') are removed. Instead, 'Mac Style Scroll Overflow' option is added.
    - 'Form Buttons' option is removed.
    - 'Enable Elementor Animations' and 'Reset Elementor Animations' options are removed. Instead, the default Elementor animation functionality is used.
    - Default value of 'Section Navigation' option is changed to 'Right'.
    - Default value of 'Scroll Overflow' option is changed to enabled.
    - Default value of 'Control Arrows' option is changed to enabled.
    - Default value of 'Control Arrows Style' option is changed to 'Modern'.
    - Default value of 'Video Autoplay' is changed to enabled.
    - New events ('beforeLeave', 'onScrollOverflow') are added.
    - New parameter (trigger) is added to 'afterLoad', 'onLeave', 'afterSlideLoad', 'onSlideLeave' events.
    - 'Observer' option is added.
    - Scroll Overflow Reset extension 'Scroll Overflow Reset Target' option is added.
    - 'Move Footer' option with footer selector is improved.
  - Experiment: Containers support is added as alpha version. Please use support for any bugs.
  - The plugin is improved and optimized.

**v1.9.0**

  - Enhancement: Toggle Header option is added under Customizations. (#125)
  - Fix: Bullet responsive CSS is fixed.
  - Fix: Compatibility update with the `Is Header A Section` option. (#127)
  - Fix: Elementor init issue is fixed. (#124)
  - Minor bug fixes and improvements.

**v1.8.3**

  - Minimum supported Elementor version is updated to v3.5.
  - Fix: WooCommerce compatibility error is fixed.
  - Minor bug fixes and improvements.

**v1.8.2**

  - Fix: Move Theme Header bug is fixed when templates are used. (#117)
  - Fix: Boxed section issue when vertical alignment option set to top is fixed. (#118)
  - Fix: Remove Js bug on frontend is fixed. (#119)
  - Fix: Initialization issue on Elementor v3.4.X is fixed. (#121)

**v1.8.1**

  - Fix: Extension script is updated to the latest. (#115)

**v1.8.0**

  - Enhancement: fullpage.js library is updated to v3.1.2. (#112)
  - Enhancement: Water effect extension support is added. (#109)
  - Enhancement: Body open hook is added to the template. (#107)
  - Enhancement: Responsive navigation options are added. (#99)
  - Fix: CSS generated by the plugin is removed when deactivated. (#111)
  - Code enhancements. (#104)
  - Minor bug fixes and improvements.

**v1.7.2**

  - Enhancement: fullpage.js library is updated to v3.1.1. (#136)
  - Minor bug fixes and improvements.

**v1.7.1**

  - Enhancement: Domain deactivation link is added on error conditions. (#39)
  - Enhancement: Js events are minified better. (#94)
  - Minor bug fixes and improvements.

**v1.7.0**

  - Enhancement: fullpage.js library is updated to v3.1.0. (#92)
  - Enhancement: Drop effect extension support is added. (#91)
  - Enhancement: Plugin settings page is reorganized. (#88)
  - Fix: Boxed section with scroll overflow fix is applied. (#90)
  - Fix: Header padding for slides is fixed. (#95)

**v1.6.0**

  - Enhancement: Tooltip background and text options are added to section options. (#82)
  - Enhancement: Hiding content before FullPage load is added as an option under Customization. (#80)
  - Fix: Editor bug is fixed. (#85)
  - Fix: Extension activated text is updated in the plugin settings page. (#84)
  - Fix: Cloned CSS codes are cleaned up. (#83)
  - FullPage extensions file is updated to the latest. (#79)
  - Minor bug fixes and improvements.

**v1.5.0**

  - Enhancement: Automatic extension installation and update functionality is added. Please remove the previous extensions plugin. You can check the [documentation](https://www.meceware.com/docs/fullpage-for-elementor/#extensions) for more details. (#63)
  - Enhancement: Events Javascript highlights is added.

**v1.4.1**

  - Minor bug fix.

**v1.4.0**

  - Enhancement: Data centered attribute for Offset Sections extension is added. (#70)
  - Enhancement: Anchor ID is removed if it's conflicted with fullpage anchor. (#68)
  - Enhancement: Support for boxed sections and slides are added. (#61)
  - Enhancement: Slides are now full width with no gap by default. Two extra steps are eliminated. (#6)
  - Fix: Fist slide animation not starting issue is fixed. (#67)
  - Fix: RTL support for Elementor editor is fixed. (#71)
  - Fix: Filled circles navigation CSS is fixed.
  - Fix: Parallax extension property is changed to translate to comply with parallax extension 0.2.3. (#66)
  - Minor improvements.

**v1.3.3**

  - Enhancement: fullpage.js library is updated to v3.0.9. (#58)
  - Fix: Data percentage bug is fixed with Offset Sections extension. (#57)
  - Fix: Template is reset on archive pages. (#55)
  - Minor improvements.

**v1.3.2**

  - Enhancement: Clickable tooltip option is added. (#53)
  - Fix: Background slide shows and background videos with overlay is fixed. (#52)
  - Fix: Fading effect extension fix. (#51)
  - Minor improvements.

**v1.3.1**

  - Fix: Parallax latest version changes are applied. Please update parallax extension script to the latest. (#47)
  - Fix: Forms button customization is added.
  - Bug: Plugin activation when white label is applied is fixed. (#45)
  - Bug: Move footer bug is fixed.

**v1.3.0**

  - Enhancement: Modern control arrow style is added. Option for control arrow color is added. (Control arrow are only available for slides.) (#43)
  - Enhancement: Missing extension parameters are added. (#40)
  - Enhancement: Full height columns support is added. (#36)
  - Bug: IE11 compatibility.
  - Bug: Elementor 2.9.x compatibility.
  - Minor improvements.

**v1.2.1**

  - Bug fixes.

**v1.2.0**

  - Enhancement: Elementor 2.9.0 compatibility. (#33)
  - Enhancement: Overlay customization is detected automatically. (#22)
  - Enhancement: Background video detected automatically.
  - Enhancement: Update server icon support is added. (#17)
  - Enhancement: Footer customization is added. (#34)
  - Enhancement: Video Keep Playing option is added. (#29)
  - Enhancement: License key is hidden when plugin is activated. (#27)
  - Enhancement: Elementor animations are detected automatically. (#28)
  - Minor Improvements and bug fixes.

**v1.1.0**

  - Enhancement: Elementor animation support is added. (#10)
  - Enhancement: Background overlay customization is added. (#15)
  - Enhancement: Header inside sections customization option is added. (#12)
  - Enhancement: Documentation link is added to the settings page. (#14)
  - Code is cleaned up. (#9)

**v1.0.1**

  - Bug: Multiple initialization is fixed. (#11)
  - Bug: Parallax transition is fixed. (#7)
  - Minor Improvements.

**v1.0.0**

  - Initial Release

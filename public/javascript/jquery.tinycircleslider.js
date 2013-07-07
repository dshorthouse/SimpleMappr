/*
 * Tiny Circleslider 1.5
 * http://www.baijs.nl/tinycircleslider/
 *
 * Copyright 2010, Maarten Baijs
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.opensource.org/licenses/gpl-2.0.php
 *
 * Date: 27 /03 / 2012
 * Depends on library: jQuery
 *
 */
;(function ($) 
{   

    $.tiny = $.tiny || {};

    $.tiny.circleslider = {
            interval     : false // move to another block on intervals.
        ,   intervaltime : 3500  // interval time in milliseconds.
        ,   snaptodots   : false // shows dots when user starts dragging and snap to them.
        ,   hidedots     : true  // fades out the dots when user stops dragging.
        ,   radius       : 140   // Used to determine the size of the circleslider
        ,   lightbox     : false // when you have links with a lightbox attached this most be true for normal links to work correctly this must be false.
        ,   callback     : null  // function that executes after every move
    };

    $.fn.tinycircleslider = function( params )
    {
        var options = $.extend({}, $.tiny.circleslider, params);

        this.each(function()
        {
            $( this ).data( "tcs", new Slider( $( this ), options ) );
        });

        return $.extend(this, {
            gotoSlide: function( slideIndex, interval )
            {
                return this.each(function ()
                {
                    $( this ).data( "tcs" ).gotoSlide( slideIndex, interval );
                });
            }
        ,   stopInterval: function()
            {
                return this.each(function ()
                {
                    $( this ).data( "tcs" ).stopInterval();
                });
            }
        ,   startInterval: function()
            {
                return this.each(function ()
                {
                    $( this ).data( "tcs" ).startInterval();
                });
            }
        });
    };

    function Slider( root, options )
    {
        var oCircle       = root
        ,   oCircleX      = oCircle.outerWidth()
        ,   oCircleY      = oCircle.outerHeight()
        ,   oThumb        = $('.thumb', oCircle)[0]
        ,   oThumbX       = $(oThumb).outerWidth()
        ,   oThumbY       = $(oThumb).outerHeight()
        ,   oOverview     = $('.overview', oCircle)
        ,   oChildren     = oOverview.children()
        ,   oDot          = {}
        ,   oLinks        = $('a',oChildren)
        ,   iPageX        = $(oChildren[0]).outerWidth(true)
        ,   oTimer2       = undefined
        ,   oTimer3       = undefined
        ,   iChildsLength = oChildren.length
        ,   angleOld      = 10
        ,   iCounter      = 0
        ,   iCurrent      = 0
        ,   self          = this;

        function setCircular()
        {
            oOverview.append( $( oChildren[0] ).clone() );
            oChildren = oOverview.children();
        }

        function setTimer( bFirst )
        {
            oTimer3 = setTimeout(function()
            {                
                self.gotoSlide( (oChildren[ (iCurrent + 1) ] !== undefined ? (iCurrent + 1) : 0), true);
            }, ( bFirst ? 50 : options.intervaltime ) );
        }

        function toRadians( degrees )
        {
            return degrees * ( Math.PI / 180 );
        }

        function toDegrees( radians )
        {
            return radians * 180 / Math.PI;
        }

        function setDots()
        {
            var $dotSnippet = $( ".dot", oCircle )
            ,   dotWidth    = $dotSnippet.outerWidth()
            ,   dotHeight   = $dotSnippet.outerHeight()
            ,   docFragment = document.createDocumentFragment()
            ,   $dotClone   = {}
            ,   dotId       = 0
            ,   dotPos      = { "left" : 0, "top" : 0 }
            ,   angle       = toRadians( 360 / iChildsLength )
            ;

            $dotSnippet.remove();

            for( var i = iChildsLength; i--; )
            {
                dotId       = i + 1;
                $dotClone   = $dotSnippet.clone();
                dotPos.top  = Math.round( -Math.cos( i * angle ) * options.radius + ( oCircleY / 2 - dotHeight / 2 ) );
                dotPos.left = Math.round(  Math.sin( i * angle ) * options.radius + ( oCircleX / 2 - dotWidth  / 2 ) );

                $dotClone.addClass( "dot-" + dotId );
                $dotClone.css( dotPos );
                $dotClone.html( "<span>" + dotId + "</span>" );

                docFragment.appendChild( $dotClone[0] );
            }

            oCircle.append( docFragment );

            oDot = $( ".dot", oCircle);
        }

        this.startInterval = function( first )
        {
            if( options.interval )
            {
                setTimer( first );
            }
        };

        this.stopInterval = function( )
        {
            clearTimeout( oTimer3 );
        };

        this.gotoSlide = function( slideIndex, interval )
        {
            var angleNew   = slideIndex * ( 360 / iChildsLength )
            ,   angleRight = 0
            ,   angleLeft  = 0
            ,   angle      = 0
            ,   framerate  = 0
            ,   angleStep  = 0
            ;

            if( angleNew > angleOld )
            {
                angleRight = angleNew - angleOld;
                angleLeft  = -( angleOld + ( 360 - angleNew) );
            }

            if( angleNew < angleOld )
            {
                angleRight = angleNew + ( 360 - angleOld );
                angleLeft  = -( angleOld - angleNew ) ;
            }

            angle     = angleRight < Math.abs( angleLeft ) ? angleRight : angleLeft;
            framerate = Math.ceil( Math.abs( angle ) / 10 );
            angleStep = ( angle / framerate ) || 0;

            stepMove( angleStep, angleNew, framerate, interval );
        };

        function sanitizeAngle( angle )
        {
            return angle + ( ( angle > 360 ) ? -360 : ( angle < 0 ) ? 360 : 0 );
        }

       function gotoClosestSlide()
        {
            var slideIndex = Math.round( angleOld / ( 360 / iChildsLength ) );

            self.gotoSlide( slideIndex );
        }

        function stepMove( angleStep, angleNew, framerate, interval )
        {
            iCounter += 1;

            var angle = sanitizeAngle( Math.round( iCounter * angleStep + angleOld ) );

            if( iCounter === framerate && interval )
            {
                self.startInterval();
            }

            setCSS(angle, iCounter === framerate);

            if( iCounter < framerate )
            {
                oTimer2 = setTimeout(function()
                {
                    stepMove( angleStep, angleNew, framerate, interval );
                }, 50);
            }
            else
            {
                iCounter = 0;
                angleOld = angleNew;
            }
        }

        function drag( event )
        {
            var thumbPos = {
                    left : event.pageX - oCircle.offset().left - ( oCircleX / 2 )
                ,   top  : event.pageY - oCircle.offset().top  - ( oCircleY / 2 )
            };

            angleOld = sanitizeAngle( toDegrees( Math.atan2( thumbPos.left, -thumbPos.top ) ) );

            setCSS( angleOld );

            return false;
        }

        function setCSS( angle, bFireCallback )
        {
            iCurrent = Math.round( angle * iChildsLength / 360 );
            iCurrent = iCurrent === iChildsLength ? 0 : iCurrent;

            oOverview[0].style.left = -( angle / 360 * iPageX * iChildsLength ) + 'px';

            oThumb.style.top  = Math.round( -Math.cos( toRadians( angle ) ) * options.radius + (oCircleY /2 - oThumbY /2)) + 'px';
            oThumb.style.left = Math.round(  Math.sin( toRadians( angle ) ) * options.radius + (oCircleX /2 - oThumbX /2)) + 'px';

            if( typeof options.callback === "function" && bFireCallback )
            {
                options.callback.call( root, oChildren[iCurrent], iCurrent );
            }
        }

        function end(oEvent)
        {
            $(document).unbind('mousemove');
            document.onmouseup = oThumb.onmouseup = null;

            clearTimeout(oTimer2);

            if( options.snaptodots )
            {
                if( options.hidedots )
                {
                    oDot.stop( true, true ).fadeOut('slow');
                }
                gotoClosestSlide();
            }

            self.startInterval();

            return false;
        }

        function start(oEvent)
        {
            clearTimeout(oTimer3);

            $(document).mousemove(drag);

            document.onmouseup = oThumb.onmouseup = end;

            if( options.snaptodots && options.hidedots )
            {
                oDot.stop(true, true).fadeIn('slow');
            }
            return false;
        }


        function setEvents()
        {
            oThumb.onmousedown = start;

            oCircle.bind( "touchstart", function( )
            {
                clearTimeout(oTimer3);

                $(this).data("pan",
                {
                        startX    : event.targetTouches[0].screenX
                    ,   lastX     : event.targetTouches[0].screenX
                    ,   startTime : new Date().getTime()
                    ,   distance  : function()
                        {
                            return Math.round((this.startX - this.lastX));
                        }
                    ,   delta: function()
                        {
                            var x     = event.targetTouches[0].screenX
                            ,   delta = Math.round((this.lastX - x))
                            ;

                            this.lastX = x;
                            return delta;
                        }
                    ,   duration: function()
                        {
                            return new Date().getTime() - this.startTime;
                        }
                });
                return false;
            });

            oCircle.bind("touchmove", function()
            {
                var pan = $(this).data('pan');
                angleOld = sanitizeAngle( angleOld + Math.round( ( pan.delta() * 2 )  * 360 / ( iPageX * iChildsLength ) ) );
                setCSS( angleOld );
                return false;
            });

            oCircle.bind("touchend", function()
            {
                var pan = $(this).data('pan');

                if( pan.distance() === 0 && pan.duration() < 500 )
                {
                    oCircle.trigger("click");
                }

                self.startInterval();

                return false;
            });

            if(oLinks.length > 0)
            {
                oCircle.css( { "cursor" : "pointer" } ).click( function( oEvent )
                {
                    if( $( oEvent.target ).hasClass( "overlay" ) )
                    {
                        if ( options.lightbox )
                        {
                            $( oLinks[ iCurrent ] ).trigger( "click" );
                        }
                        else
                        {
                            location.href = oLinks[ iCurrent ].href;
                        }
                    }
                    return false;
                });
            }

            if( options.snaptodots )
            {
                oCircle.delegate( ".dot", "click", function()
                {
                    clearTimeout(oTimer3);

                    if( 0 === iCounter)
                    {
                        self.gotoSlide( $( this ).text() - 1 );
                    }

                    self.startInterval();
                });
            }
        }

        function initialize()
        {
            setCircular();

            oOverview[0].style.width = iPageX * oChildren.length +'px';

            if( options.snaptodots )
            {
                setDots();
            }

            setEvents();

            self.gotoSlide(0);

            self.startInterval( true );
        }

        initialize();
    }
}(jQuery));
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Package JishiGou $
 *
 * @Filename reg.js $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:36 1458085298 1933369651 3318 $
 *******************************************************************/


(function($) {
    $.fn.doubleSelect = function(c, d, e) {
        e = $.extend({
            preselectFirst: null,
            preselectSecond: null,
            emptyOption: false,
            emptyKey: -1,
            emptyValue: "Choose ..."

        },
        e || {});
        var f = this;
        var g = "#" + c;
        var h = $(g);
        var i = function(a) {
            h.val(a).change()

        };
        var j = function() {
            $(g + " option").remove()

        };
        $(this).change(function() {
            j();
            $current = this.options[this.selectedIndex].value;
            if ($current != "") {
                $.each(d, 
                function(k, v) {
                    if ($current == v.key) {
                        $.each(v.values, 
                        function(k, a) {
                            var o = $("<option>").html(k).attr("value", a);
                            if (v.defaultvalue != null && a == v.defaultvalue) {
                                o.html(k).attr("selected", "selected")

                            }
                            if (e.preselectSecond != null && a == e.preselectSecond) {
                                o.html(k).attr("selected", "selected")

                            }
                            o.appendTo(h)

                        })

                    }

                })

            } else {
                i(e.emptyValue)

            }

        });
        return this.each(function() {
            f.children().remove();
            h.children().remove();
            if (e.emptyOption) {
                var b = $("<option>").html(e.emptyValue).attr("value", e.emptyKey);
                b.appendTo(f)

            }
            $.each(d, 
            function(k, v) {
                var a = $("<option>").html(k).attr("value", v.key);
                if (e.preselectFirst != null && v.key == e.preselectFirst) {
                    a.html(k).attr("selected", "selected")

                }
                a.appendTo(f)

            });
            if (e.preselectFirst == null) {
                $current = this.options[this.selectedIndex].value;
                if ($current != "") {
                    $.each(d, 
                    function(k, v) {
                        if ($current == v.key) {
                            $.each(v.values, 
                            function(k, a) {
                                var o = $("<option>").html(k).attr("value", a);
                                if (v.defaultvalue != null && a == v.defaultvalue) {
                                    o.html(k).attr("selected", "selected")

                                }
                                if (e.preselectSecond != null && a == e.preselectSecond) {
                                    o.html(k).attr("selected", "selected")

                                }
                                o.appendTo(h)

                            })

                        }

                    })

                } else {
                    i(e.emptyValue)

                }

            } else {
                f.change()

            }

        })

    }

})(jQuery);
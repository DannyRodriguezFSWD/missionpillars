const MPHelper = {
    
    /************* AJAX ********************/
    redirectToLogin() {
        window.location.href = "/login";
    },
    
    /**
     * Handles Unauthenticated error returned by an Ajax call
     * @param  {object}   e        
     * @param  {null|boolean|Function} callback Optional. A function to be called if error is Unauthenticated. If null, does nothing (allows handling via a conditional check on the return value). Otherwise, will call redirectToLogin
     * @return {boolean}            False if error is not 'Unauthenticated.'. True otherwise
     */
    handleIfUnauthenticated(e, callback) {
        if (e.status != 401) return false;
        if (callback === null) return true;
        if (typeof(callback) != 'function') {
            this.redirectToLogin();
            return true;
        }
        
        callback()
        return true;
    },
    
    /**
     * Sets a 'heartbeat' to see if session is still alive. Only allows one heartbeat to be running on the page.
     * @param  {integer}   seconds        
     * @param  {null|boolean|Function} callback Optional. Accepts same values as handleIfUnauthenticated with same result if session is not live
     */
    setHeartbeat(seconds, callback) {
        this.stopHeartbeat()
        
        this.setHeartbeat.lastIntervalID = setInterval(function () { 
            $.get('/crm/ajax/heartbeat', function () {
                // console.log('beat');
            })
            .fail(function (e) {
                if (MPHelper.handleIfUnauthenticated(e, callback)) {
                    // console.log('Heartbeat stopped')
                    MPHelper.stopHeartbeat()
                }
            });
        }, seconds*1000)
    },
    
    /**
     * Stops heartbeat if it is ticking allowing for no or alternative handling of unautheticated sessions
     */
    stopHeartbeat() {
        if (MPHelper.setHeartbeat.lastIntervalID) {
            clearInterval(MPHelper.setHeartbeat.lastIntervalID)
            MPHelper.setHeartbeat.lastIntervalID = null
        }
    },
    
    /************* DATE / TIME *************/
    /**
     * Returns a new date set to the current date. See https://24timezones.com/timezone-map
     * @param  {integer} hour Optional. The UTC hour of the day, defaults to 11am
     * @return {Date}      
     */
    getTodayWithUTCTime(hour) {
        
        var temp = new Date; 
        if (typeof(hour) != "undefined") {
            this.setTime(temp, hour, 0, 0)
        } else this.setDefaultTime(temp)
    
        return temp
    },
    getToday() {
        return this.getTodayWithUTCTime();
    },
    setTime(d, hour, minute, second) {
        d.setUTCHours(hour); 
        d.setUTCMinutes(minute); 
        d.setSeconds(second); 
        
        return d;
    },
    setDefaultTime(d) {
        hour = 11; // all around the world 11am UTC is still the same day
        return this.setTime(d, hour, 0, 0)
    },
    
    
    /************* STRINGS *************/
    // mimics str_limit
    limit(string, chars, append) {
        if (string.length < chars) return string
        if (!append) append = "..."
        return string.substring(0,chars) + append
    },
    
    // mimics str_limit_middle defined in helpers.php
    limit_middle(string, chars, replace) {
        if (typeof replace != 'string') replace = "..."
        if (string.length < chars) return string
        
        return this.limit(string, chars/2 - 1,replace+string.substr(-chars/2));
    },
    
    
    /************* CURRENCY *************/
    
    /**
     * @param  {[string|int|float]} amount  Dollar amount
     * @param  {[bollean]} abs    Optional. If specified amount returned is the absolute value
     * @return {[type]}        Amount expressed in cents
     */
    dollarsToCents(amount, abs) {
        var cents =  Number.parseInt(Math.round(amount*100))
        return abs ? Math.abs(cents) : cents
    },
    
    /**
     * @param  {[string|int|float]} amount  Amount in cents
     * @param  {[bollean]} abs    Optional. If specified amount returned is the absolute value
     * @return {[type]}        Amount expressed in dollars
     */
    centsToDollars(amount) {
        var dollars =  Number.parseFloat(Number.parseInt(amount)/100.0)
        return abs ? Math.abas(dollars) : dollars
    },
    
    
} 
$(function () {
    // if authenticated, start the 'old ticker'
//    $.get('/crm/ajax/heartbeat', function () {
//        setTimeout(function () { 
//            if ( !MPHelper.setHeartbeat.lastIntervalID ) {
//                MPHelper.setHeartbeat(30) 
//            }
//        }, 30000)
//    })
})

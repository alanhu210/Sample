
interface TemperatureWatcher {
	/**
	 * Intial or out of alert range (alert removed)
	 */
	public static final int ALERT_STATE_NONE = 0;
	/**
	 * Alerted and still in alert range
	 */
	public static final int ALERT_STATE_ALERTED = 1;

	public void alert(TemperatureReading currentTemperature);

	public Threshold getThreshold ();

	public void setThreshold(Threshold threshold);

	public int getAlertState ();

	public void setAlertState (int state);
	
}
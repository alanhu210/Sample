import java.math.BigDecimal;
import java.util.ArrayList;

public class Thermometer {
	
	private ArrayList<TemperatureWatcher> temperatureWatchers;

	/**
	 * The scale used to represent tempreature internally
	 */
	private TemperatureScale temperatureScale;

	private TemperatureReading previousReading;

	private TemperatureReading currentReading;

	private int direction;

	/**
	 * Constructs a Thermometer instance with the default scale TemperatureScale.CELSIUS
	 */
	public Thermometer() {
		this(TemperatureScale.CELSIUS);
	}

	public Thermometer(TemperatureScale scale) {
		this.temperatureScale = scale;
		this.currentReading = null;
		this.previousReading = null;
		this.direction = Threshold.MOVING_NONE;
		this.temperatureWatchers = new ArrayList();
	}

	public void addTemperatureWatcher(TemperatureWatcher watcher) {
		temperatureWatchers.add(watcher);
	}

	public TemperatureScale getTemperatureScale() {
		return temperatureScale;
	}

	public synchronized TemperatureReading getCurrentReading() {
		return currentReading;
	}

	public synchronized TemperatureReading getCurrentReading(TemperatureScale scale) {
		if (scale == this.temperatureScale) {
			return currentReading;
		} else {
			TemperatureReading newReading = new TemperatureReading();
			newReading.setTemperature(currentReading.getTemperature().convertTo(scale));
			return newReading;
		}
	}

	public synchronized TemperatureReading getPreviousReading() {
		return previousReading;
	}

	public synchronized TemperatureReading getPreviousReading(TemperatureScale scale) {
		if (scale == this.temperatureScale) {
			return previousReading;
		} else {
			TemperatureReading newReading = new TemperatureReading();
			newReading.setTemperature(previousReading.getTemperature().convertTo(scale));
			return newReading;
		}
	}

	public synchronized void setTemperatureReading(double temperature) {
		this.setTemperatureReading(new BigDecimal(temperature));
	}

	public synchronized void setTemperatureReading(BigDecimal temperature) {
		this.setTemperatureReading(new Temperature(temperatureScale, temperature));
	}

	protected synchronized void setTemperatureReading(Temperature temperature) {
		previousReading = currentReading;
		currentReading = new TemperatureReading();
		currentReading.setId(System.currentTimeMillis());
		currentReading.setTemperature(temperature);

		setDirection();

		notifyTemperatureWatchers();
	}

	protected void notifyTemperatureWatchers() {
		for (int i = 0; i < temperatureWatchers.size(); i++) {
			TemperatureWatcher watcher = temperatureWatchers.get(i);
			if (isThresholdReached(watcher)) {
				watcher.alert(currentReading);
				watcher.setAlertState (watcher.ALERT_STATE_ALERTED);
			}
		}
	}

	protected boolean isThresholdReached(TemperatureWatcher watcher) {

		Threshold threshold = watcher.getThreshold();

		TemperatureReading current = currentReading;
		BigDecimal temperatureThreshold = threshold.getThreshold().getDegrees();

		if (threshold.getThreshold().getTemperatureScale() != this.temperatureScale) {
			current = getCurrentReading(threshold.getThreshold().getTemperatureScale());
		}

		BigDecimal currentTemperature = current.getTemperature().getDegrees();

		// Alert State: check if current temperature is out of the watcher's alert range 
		BigDecimal margin = threshold.getMargin();

		if (margin != null && margin.compareTo(BigDecimal.ZERO) > 0) {
			BigDecimal low = temperatureThreshold.subtract(threshold.getMargin());
			BigDecimal high = temperatureThreshold.add(threshold.getMargin());
			// alert removed
			if (currentTemperature.compareTo(low) < 0 || currentTemperature.compareTo(high) > 0) {
				watcher.setAlertState(watcher.ALERT_STATE_NONE);
				return false;
			}
		}

		if (currentTemperature.compareTo(temperatureThreshold) == 0 
			&& checkState(watcher) 
			&& checkDirection(threshold.getDirection())) {

			return true;
		}

		return false;
	}

	/**
	 * If threshold margin is set, we need to check if we can send alert again
	 * @param  watcher
	 * @return boolean
	 */
	private boolean checkState(TemperatureWatcher watcher) {
		BigDecimal margin = watcher.getThreshold().getMargin();
		return margin == null 
			|| margin.compareTo(BigDecimal.ZERO) == 0 
			|| watcher.getAlertState() == watcher.ALERT_STATE_NONE;
	}

	private boolean checkDirection(int direction) {
		return direction == Threshold.MOVING_NONE || direction == this.direction;
	}

	private void setDirection() {

		if (previousReading == null) {
			return;
		}

		BigDecimal currentTemperature = currentReading.getTemperature().getDegrees();
		BigDecimal previousTemperature = previousReading.getTemperature().getDegrees();

		int compare = currentTemperature.compareTo(previousTemperature);
		if (compare > 0) {
			this.direction = Threshold.MOVING_UP;
		} else if (compare < 0) {
			this.direction = Threshold.MOVING_DOWN;
		} else {
			this.direction = Threshold.MOVING_NONE;
		}
	}
}
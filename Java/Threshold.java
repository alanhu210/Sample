import java.math.BigDecimal;

public class Threshold {

	public static final int MOVING_NONE = 0;
	public static final int MOVING_UP = 1;
	public static final int MOVING_DOWN = 2;
	
	/**
	 * temperature point at which callers will be alerted
	 */
	private Temperature threshold;
	/**
	 * temperature fluctuation from the threshold
	 * If set, no alerts if temp readings have been within the range of threshold-margin and threshold+margin
	 * If not set, alerts will be sent if the temperature reaches the threshold point
	 */
	private BigDecimal margin;
	/**
	 * whether to send alert depending on whether temperature is dropping or rising 
	 */
	private int direction;

	public Threshold (Temperature threshold) {
		this(threshold, null);
	}

	public Threshold (Temperature threshold, BigDecimal margin) {
		this(threshold, margin, MOVING_NONE);
	}

	public Threshold (Temperature threshold, BigDecimal margin, int direction) throws IllegalArgumentException {
		if (direction != MOVING_UP && direction != MOVING_DOWN && direction != MOVING_NONE) {
			throw new IllegalArgumentException("invlaid direction");
		}
		this.threshold = threshold;
		this.margin = margin;
		this.direction = direction;
	}

	public Temperature getThreshold() {
		return threshold;
	}

	public void setThreshold(Temperature threshold) {
		this.threshold = threshold;
	}

	public BigDecimal getMargin() {
		return margin;
	}

	public void setMargin(BigDecimal margin) throws IllegalArgumentException {
		if (margin.compareTo(BigDecimal.ZERO) < 0) {
			throw new IllegalArgumentException("margin must be positive or zero");
		}
		this.margin = margin;
	}

	public int getDirection() {
		return direction;
	}

	public void setDirection(int direction) {
		this.direction = direction;
	}
}
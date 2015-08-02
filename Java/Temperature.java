import java.math.BigDecimal;

public class Temperature {

	private BigDecimal degrees;
	private TemperatureScale scale;

	public Temperature(TemperatureScale scale, BigDecimal degrees) {
		this.scale =  scale;
		this.degrees = degrees;
	}

	public BigDecimal getDegrees() {
		return degrees;
	}

	public TemperatureScale getTemperatureScale() {
		return scale;
	}

	/**
	 * [convertTo description]
	 * @param  toScale [description]
	 * @return         [description]
	 */
	public Temperature convertTo(TemperatureScale toScale) {

		if (this.scale == toScale){
			return this;
		}
		BigDecimal value = null;
		if (this.scale == TemperatureScale.CELSIUS){
			value = degrees.multiply(new BigDecimal(9)).divide(new BigDecimal(5)).add(new BigDecimal(32));
		} else {
			value = degrees.subtract(new BigDecimal(32)).multiply(new BigDecimal(9)).divide(new BigDecimal(5));
		}

		return new Temperature(toScale, value);
	}

	public String toString() {
		return degrees + " " + (scale == TemperatureScale.CELSIUS ? "C" : "F");
	}
}
